"use client";

import { createContext, useContext, useState, useCallback, useEffect, type ReactNode } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { getCart, addToCart, updateCartItem, removeCartItem, clearCart, mergeCart } from "@/lib/api/cart";
import { useAuth } from "@/lib/auth-context";
import type { Cart, CartItem } from "@/types";

interface CartContextValue {
  cart: Cart | null;
  itemCount: number;
  isLoading: boolean;
  addItem: (productId: number, quantity?: number) => Promise<void>;
  updateItem: (itemId: number, quantity: number) => Promise<void>;
  removeItem: (itemId: number) => Promise<void>;
  clear: () => Promise<void>;
}

const CART_STORAGE_KEY = "vestra_cart";

interface GuestCartItem {
  product_id: number;
  quantity: number;
}

function getGuestCart(): GuestCartItem[] {
  if (typeof window === "undefined") return [];
  try {
    const raw = localStorage.getItem(CART_STORAGE_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch {
    return [];
  }
}

function setGuestCart(items: GuestCartItem[]) {
  if (typeof window === "undefined") return;
  localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(items));
}

function addToGuestCart(productId: number, quantity: number) {
  const items = getGuestCart();
  const existing = items.find((i) => i.product_id === productId);
  if (existing) {
    existing.quantity += quantity;
  } else {
    items.push({ product_id: productId, quantity });
  }
  setGuestCart(items);
}

function updateGuestCartItem(productId: number, quantity: number) {
  const items = getGuestCart();
  const existing = items.find((i) => i.product_id === productId);
  if (existing) {
    existing.quantity = quantity;
    if (existing.quantity <= 0) {
      setGuestCart(items.filter((i) => i.product_id !== productId));
      return;
    }
  }
  setGuestCart(items);
}

function removeFromGuestCart(productId: number) {
  const items = getGuestCart().filter((i) => i.product_id !== productId);
  setGuestCart(items);
}

function clearGuestCart() {
  setGuestCart([]);
}

const CartContext = createContext<CartContextValue | null>(null);

export function CartProvider({ children }: { children: ReactNode }) {
  const queryClient = useQueryClient();
  const { isAuthenticated } = useAuth();
  const [guestCart, setGuestCartState] = useState<GuestCartItem[]>(getGuestCart);

  const { data: serverCart, isLoading } = useQuery({
    queryKey: ["cart"],
    queryFn: getCart,
    enabled: isAuthenticated,
    staleTime: 0,
  });

  // Merge guest cart on login
  useEffect(() => {
    if (isAuthenticated && guestCart.length > 0) {
      mergeCart(guestCart).then(() => {
        clearGuestCart();
        setGuestCartState([]);
        queryClient.invalidateQueries({ queryKey: ["cart"] });
      });
    }
  }, [isAuthenticated]);

  const addMutation = useMutation({
    mutationFn: ({ productId, quantity }: { productId: number; quantity: number }) =>
      addToCart(productId, quantity),
  });

  const updateMutation = useMutation({
    mutationFn: ({ itemId, quantity }: { itemId: number; quantity: number }) =>
      updateCartItem(itemId, quantity),
  });

  const removeMutation = useMutation({
    mutationFn: (itemId: number) => removeCartItem(itemId),
  });

  const clearMutation = useMutation({
    mutationFn: clearCart,
  });

  const addItem = useCallback(
    async (productId: number, quantity: number = 1) => {
      if (isAuthenticated) {
        await addMutation.mutateAsync({ productId, quantity });
        queryClient.invalidateQueries({ queryKey: ["cart"] });
      } else {
        addToGuestCart(productId, quantity);
        setGuestCartState(getGuestCart());
      }
    },
    [isAuthenticated, addMutation, queryClient]
  );

  const updateItem = useCallback(
    async (itemId: number, quantity: number) => {
      if (isAuthenticated) {
        await updateMutation.mutateAsync({ itemId, quantity });
        queryClient.invalidateQueries({ queryKey: ["cart"] });
      } else {
        // For guest, itemId is actually productId
        updateGuestCartItem(itemId, quantity);
        setGuestCartState(getGuestCart());
      }
    },
    [isAuthenticated, updateMutation, queryClient]
  );

  const removeItem = useCallback(
    async (itemId: number) => {
      if (isAuthenticated) {
        await removeMutation.mutateAsync(itemId);
        queryClient.invalidateQueries({ queryKey: ["cart"] });
      } else {
        removeFromGuestCart(itemId);
        setGuestCartState(getGuestCart());
      }
    },
    [isAuthenticated, removeMutation, queryClient]
  );

  const clear = useCallback(async () => {
    if (isAuthenticated) {
      await clearMutation.mutateAsync();
      queryClient.invalidateQueries({ queryKey: ["cart"] });
    } else {
      clearGuestCart();
      setGuestCartState([]);
    }
  }, [isAuthenticated, clearMutation, queryClient]);

  // Build a Cart-like object for guests
  const cart: Cart | null = isAuthenticated
    ? serverCart ?? null
    : {
        id: 0,
        items: guestCart.map((g, index) => ({
          id: index + 1,
          product_id: g.product_id,
          quantity: g.quantity,
          line_total: "0",
          product: null as unknown as CartItem["product"],
        })),
        item_count: guestCart.reduce((sum, g) => sum + g.quantity, 0),
        subtotal: "0",
      };

  const value: CartContextValue = {
    cart,
    itemCount: cart?.item_count ?? 0,
    isLoading: isAuthenticated ? isLoading : false,
    addItem,
    updateItem,
    removeItem,
    clear,
  };

  return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
}

export function useCartContext() {
  const context = useContext(CartContext);
  if (!context) {
    throw new Error("useCartContext must be used within CartProvider");
  }
  return context;
}
