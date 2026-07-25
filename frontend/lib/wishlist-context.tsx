"use client";

import {
  createContext,
  useContext,
  useState,
  useCallback,
  useEffect,
  useMemo,
  useRef,
  type ReactNode,
} from "react";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { useAuth } from "@/lib/auth-context";
import {
  getWishlist,
  addToWishlist as addToWishlistApi,
  removeFromWishlist as removeFromWishlistApi,
  moveWishlistToCart as moveWishlistToCartApi,
  mergeWishlist,
} from "@/lib/api/wishlist";
import {
  getSavedItems,
  addToSavedItems as addToSavedItemsApi,
  removeFromSavedItems as removeFromSavedItemsApi,
  moveSavedItemToCart as moveSavedItemToCartApi,
  mergeSavedItems,
} from "@/lib/api/saved-items";
import { toastError, toastSuccess } from "@/lib/toast-utils";
import type { Product } from "@/types";
import type { WishlistItem } from "@/lib/api/wishlist";
import type { SavedItem } from "@/lib/api/saved-items";

interface GuestWishlistItem {
  product: Product;
  list_name: string;
  notes?: string;
}

interface GuestSavedItem {
  product: Product;
}

interface WishlistContextValue {
  wishlist: WishlistItem[];
  savedItems: SavedItem[];
  isLoading: boolean;
  isInWishlist: (productId: number) => boolean;
  isSavedForLater: (productId: number) => boolean;
  addToWishlist: (product: Product, listName?: string, notes?: string) => Promise<void>;
  removeFromWishlist: (productId: number) => Promise<void>;
  moveWishlistToCart: (productId: number) => Promise<void>;
  addToSavedItems: (product: Product) => Promise<void>;
  removeFromSavedItems: (productId: number) => Promise<void>;
  moveSavedItemToCart: (productId: number) => Promise<void>;
}

const WISHLIST_STORAGE_KEY = "vestra_wishlist";
const SAVED_STORAGE_KEY = "vestra_saved_items";

const WishlistContext = createContext<WishlistContextValue | null>(null);

function getGuestWishlist(): GuestWishlistItem[] {
  if (typeof window === "undefined") return [];
  try {
    const raw = localStorage.getItem(WISHLIST_STORAGE_KEY);
    const parsed = raw ? JSON.parse(raw) : [];
    return Array.isArray(parsed) ? parsed : [];
  } catch {
    return [];
  }
}

function setGuestWishlist(items: GuestWishlistItem[]) {
  if (typeof window === "undefined") return;
  try {
    localStorage.setItem(WISHLIST_STORAGE_KEY, JSON.stringify(items));
  } catch {
    toastError("Could not save your wishlist. Storage may be full.");
  }
}

function getGuestSavedItems(): GuestSavedItem[] {
  if (typeof window === "undefined") return [];
  try {
    const raw = localStorage.getItem(SAVED_STORAGE_KEY);
    const parsed = raw ? JSON.parse(raw) : [];
    return Array.isArray(parsed) ? parsed : [];
  } catch {
    return [];
  }
}

function setGuestSavedItems(items: GuestSavedItem[]) {
  if (typeof window === "undefined") return;
  try {
    localStorage.setItem(SAVED_STORAGE_KEY, JSON.stringify(items));
  } catch {
    toastError("Could not save your items. Storage may be full.");
  }
}

export function WishlistProvider({ children }: { children: ReactNode }) {
  const queryClient = useQueryClient();
  const { isAuthenticated } = useAuth();
  const [guestWishlist, setGuestWishlistState] = useState<GuestWishlistItem[]>(getGuestWishlist);
  const [guestSavedItems, setGuestSavedItemsState] = useState<GuestSavedItem[]>(getGuestSavedItems);
  const mergeTriggeredRef = useRef(false);

  const { data: serverWishlistData, isLoading: wishlistLoading } = useQuery({
    queryKey: ["wishlist"],
    queryFn: () => getWishlist(1),
    enabled: isAuthenticated,
    staleTime: 0,
  });

  const { data: serverSavedData, isLoading: savedLoading } = useQuery({
    queryKey: ["saved-items"],
    queryFn: () => getSavedItems(1),
    enabled: isAuthenticated,
    staleTime: 0,
  });

  const mergeGuestData = useCallback(async () => {
    if (mergeTriggeredRef.current) return;
    const wishlistItems = getGuestWishlist();
    const savedItems = getGuestSavedItems();
    if (wishlistItems.length === 0 && savedItems.length === 0) return;

    mergeTriggeredRef.current = true;
    try {
      if (wishlistItems.length > 0) {
        await mergeWishlist(
          wishlistItems.map((item) => ({
            product_id: item.product.id,
            list_name: item.list_name,
            notes: item.notes,
          }))
        );
      }
      if (savedItems.length > 0) {
        await mergeSavedItems(savedItems.map((item) => ({ product_id: item.product.id })));
      }
      setGuestWishlist([]);
      setGuestWishlistState([]);
      setGuestSavedItems([]);
      setGuestSavedItemsState([]);
      queryClient.invalidateQueries({ queryKey: ["wishlist"] });
      queryClient.invalidateQueries({ queryKey: ["saved-items"] });
    } catch {
      mergeTriggeredRef.current = false;
      toastError("Failed to merge your saved items. Please try again.");
    }
  }, [queryClient]);

  useEffect(() => {
    if (isAuthenticated && (guestWishlist.length > 0 || guestSavedItems.length > 0) && !mergeTriggeredRef.current) {
      mergeGuestData();
    }
    if (!isAuthenticated) {
      mergeTriggeredRef.current = false;
    }
  }, [isAuthenticated, guestWishlist, guestSavedItems, mergeGuestData]);

  const wishlist: WishlistItem[] = useMemo(
    () =>
      isAuthenticated
        ? serverWishlistData?.items ?? []
        : guestWishlist.map((item) => ({
            id: item.product.id,
            list_name: item.list_name,
            notes: item.notes ?? null,
            product: item.product,
            created_at: new Date().toISOString(),
          })),
    [isAuthenticated, serverWishlistData, guestWishlist]
  );

  const savedItems: SavedItem[] = useMemo(
    () =>
      isAuthenticated
        ? serverSavedData?.items ?? []
        : guestSavedItems.map((item) => ({
            id: item.product.id,
            product: item.product,
            created_at: new Date().toISOString(),
          })),
    [isAuthenticated, serverSavedData, guestSavedItems]
  );

  const isInWishlist = useCallback(
    (productId: number) => wishlist.some((item) => item.product.id === productId),
    [wishlist]
  );

  const isSavedForLater = useCallback(
    (productId: number) => savedItems.some((item) => item.product.id === productId),
    [savedItems]
  );

  const addToWishlist = useCallback(
    async (product: Product, listName = "Default", notes?: string) => {
      if (isAuthenticated) {
        try {
          await addToWishlistApi(product.id, listName, notes);
          queryClient.invalidateQueries({ queryKey: ["wishlist"] });
          toastSuccess("Added to wishlist");
        } catch (error) {
          const message = error instanceof Error ? error.message : "Could not add to wishlist.";
          toastError(message);
          throw error;
        }
      } else {
        const items = getGuestWishlist();
        if (items.some((item) => item.product.id === product.id && item.list_name === listName)) {
          toastError("This product is already in your wishlist.");
          throw new Error("Already in wishlist");
        }
        items.push({ product, list_name: listName, notes });
        setGuestWishlist(items);
        setGuestWishlistState([...items]);
        toastSuccess("Added to wishlist");
      }
    },
    [isAuthenticated, queryClient]
  );

  const removeFromWishlist = useCallback(
    async (productId: number) => {
      if (isAuthenticated) {
        try {
          await removeFromWishlistApi(productId);
          queryClient.invalidateQueries({ queryKey: ["wishlist"] });
        } catch (error) {
          const message = error instanceof Error ? error.message : "Could not remove from wishlist.";
          toastError(message);
          throw error;
        }
      } else {
        const items = getGuestWishlist().filter((item) => item.product.id !== productId);
        setGuestWishlist(items);
        setGuestWishlistState([...items]);
      }
    },
    [isAuthenticated, queryClient]
  );

  const moveWishlistToCart = useCallback(
    async (productId: number) => {
      if (isAuthenticated) {
        try {
          await moveWishlistToCartApi(productId);
          queryClient.invalidateQueries({ queryKey: ["wishlist"] });
          queryClient.invalidateQueries({ queryKey: ["cart"] });
          toastSuccess("Moved to cart");
        } catch (error) {
          const message = error instanceof Error ? error.message : "Could not move item to cart.";
          toastError(message);
          throw error;
        }
      } else {
        toastError("Please sign in to move items to your cart.");
        throw new Error("Not authenticated");
      }
    },
    [isAuthenticated, queryClient]
  );

  const addToSavedItems = useCallback(
    async (product: Product) => {
      if (isAuthenticated) {
        try {
          await addToSavedItemsApi(product.id);
          queryClient.invalidateQueries({ queryKey: ["saved-items"] });
          toastSuccess("Saved for later");
        } catch (error) {
          const message = error instanceof Error ? error.message : "Could not save item.";
          toastError(message);
          throw error;
        }
      } else {
        const items = getGuestSavedItems();
        if (items.some((item) => item.product.id === product.id)) {
          toastError("This product is already saved for later.");
          throw new Error("Already saved");
        }
        items.push({ product });
        setGuestSavedItems(items);
        setGuestSavedItemsState([...items]);
        toastSuccess("Saved for later");
      }
    },
    [isAuthenticated, queryClient]
  );

  const removeFromSavedItems = useCallback(
    async (productId: number) => {
      if (isAuthenticated) {
        try {
          await removeFromSavedItemsApi(productId);
          queryClient.invalidateQueries({ queryKey: ["saved-items"] });
        } catch (error) {
          const message = error instanceof Error ? error.message : "Could not remove saved item.";
          toastError(message);
          throw error;
        }
      } else {
        const items = getGuestSavedItems().filter((item) => item.product.id !== productId);
        setGuestSavedItems(items);
        setGuestSavedItemsState([...items]);
      }
    },
    [isAuthenticated, queryClient]
  );

  const moveSavedItemToCart = useCallback(
    async (productId: number) => {
      if (isAuthenticated) {
        try {
          await moveSavedItemToCartApi(productId);
          queryClient.invalidateQueries({ queryKey: ["saved-items"] });
          queryClient.invalidateQueries({ queryKey: ["cart"] });
          toastSuccess("Moved to cart");
        } catch (error) {
          const message = error instanceof Error ? error.message : "Could not move item to cart.";
          toastError(message);
          throw error;
        }
      } else {
        toastError("Please sign in to move items to your cart.");
        throw new Error("Not authenticated");
      }
    },
    [isAuthenticated, queryClient]
  );

  return (
    <WishlistContext.Provider
      value={{
        wishlist,
        savedItems,
        isLoading: wishlistLoading || savedLoading,
        isInWishlist,
        isSavedForLater,
        addToWishlist,
        removeFromWishlist,
        moveWishlistToCart,
        addToSavedItems,
        removeFromSavedItems,
        moveSavedItemToCart,
      }}
    >
      {children}
    </WishlistContext.Provider>
  );
}

export function useWishlist() {
  const context = useContext(WishlistContext);
  if (!context) {
    throw new Error("useWishlist must be used within a WishlistProvider");
  }
  return context;
}
