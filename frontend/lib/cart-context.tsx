"use client";

import { createContext, useContext, useState, useCallback, useEffect, useRef, type ReactNode } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { getCart, addToCart, updateCartItem, removeCartItem, clearCart, mergeCart } from "@/lib/api/cart";
import { useAuth } from "@/lib/auth-context";
import type { Cart, CartItem, CartItemProduct, Product } from "@/types";
import { toastError, toastStockLimitReached } from "@/lib/toast-utils";

export function toCartProduct(product: Product): CartItemProduct {
  return {
    id: product.id,
    name: product.name,
    slug: product.slug,
    sku: product.sku,
    price: product.price,
    stock_quantity: product.stock_quantity,
    images: product.images,
  };
}

interface CartContextValue {
  cart: Cart | null;
  itemCount: number;
  isLoading: boolean;
  addItem: (product: CartItemProduct, quantity?: number) => Promise<void>;
  updateItem: (itemId: number, quantity: number) => Promise<void>;
  removeItem: (itemId: number) => Promise<void>;
  clear: () => Promise<void>;
  mergeGuestCart: () => Promise<void>;
}

const CART_STORAGE_KEY = "vestra_cart";

interface GuestCartItem {
  product: CartItemProduct;
  quantity: number;
}

function getGuestCart(): GuestCartItem[] {
  if (typeof window === "undefined") return [];
  try {
    const raw = localStorage.getItem(CART_STORAGE_KEY);
    const parsed = raw ? JSON.parse(raw) : [];
    return Array.isArray(parsed) ? parsed : [];
  } catch {
    return [];
  }
}

function setGuestCart(items: GuestCartItem[]) {
  if (typeof window === "undefined") return;
  try {
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(items));
  } catch {
    toastError("Could not save your cart. Storage may be full.");
  }
}

function calculateLineTotal(price: string, quantity: number): string {
  return (Number(price || 0) * quantity).toFixed(2);
}

function buildGuestCart(items: GuestCartItem[]): Cart {
  const cartItems: CartItem[] = items.map((item) => ({
    id: item.product.id,
    product: item.product,
    quantity: item.quantity,
    line_total: calculateLineTotal(item.product.price, item.quantity),
  }));

  const subtotal = items
    .reduce((sum, item) => sum + Number(item.product.price || 0) * item.quantity, 0)
    .toFixed(2);

  return {
    id: 0,
    items: cartItems,
    item_count: items.reduce((sum, item) => sum + item.quantity, 0),
    subtotal,
  };
}

const CartContext = createContext<CartContextValue | null>(null);

export function CartProvider({ children }: { children: ReactNode }) {
  const queryClient = useQueryClient();
  const { isAuthenticated } = useAuth();
  const [guestCart, setGuestCartState] = useState<GuestCartItem[]>(getGuestCart);
  const mergeTriggeredRef = useRef(false);

  const { data: serverCart, isLoading } = useQuery({
    queryKey: ["cart"],
    queryFn: getCart,
    enabled: isAuthenticated,
    staleTime: 0,
  });

  const mergeGuestCart = useCallback(async () => {
    if (mergeTriggeredRef.current) return;
    const items = getGuestCart();
    if (items.length === 0) return;

    mergeTriggeredRef.current = true;
    const itemsToMerge = items.map((item) => ({
      product_id: item.product.id,
      quantity: item.quantity,
    }));

    try {
      await mergeCart(itemsToMerge);
      setGuestCart([]);
      setGuestCartState([]);
      queryClient.invalidateQueries({ queryKey: ["cart"] });
    } catch {
      mergeTriggeredRef.current = false;
      toastError("Failed to merge your guest cart. Please try again.");
      throw new Error("Merge failed");
    }
  }, [queryClient]);

  // Merge guest cart on login exactly once
  useEffect(() => {
    if (isAuthenticated && guestCart.length > 0 && !mergeTriggeredRef.current) {
      mergeGuestCart();
    }
    if (!isAuthenticated) {
      mergeTriggeredRef.current = false;
    }
  }, [isAuthenticated, guestCart, mergeGuestCart]);

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
    async (product: CartItemProduct, quantity: number = 1) => {
      const requestedQuantity = Math.max(1, quantity);

      if (product.stock_quantity <= 0) {
        toastError("This product is out of stock.");
        throw new Error("Out of stock");
      }

      if (isAuthenticated) {
        try {
          await addMutation.mutateAsync({ productId: product.id, quantity: requestedQuantity });
          queryClient.invalidateQueries({ queryKey: ["cart"] });
        } catch (error) {
          const message = error instanceof Error ? error.message : "Could not add item to cart.";
          toastError(message);
          throw error;
        }
      } else {
        const items = getGuestCart();
        const existing = items.find((item) => item.product.id === product.id);
        const currentQuantity = existing ? existing.quantity : 0;
        const newQuantity = currentQuantity + requestedQuantity;

        if (newQuantity > product.stock_quantity) {
          toastStockLimitReached(product.stock_quantity);
          throw new Error("Stock limit reached");
        }

        if (existing) {
          existing.quantity = newQuantity;
        } else {
          items.push({ product, quantity: requestedQuantity });
        }

        setGuestCart(items);
        setGuestCartState([...items]);
      }
    },
    [isAuthenticated, addMutation, queryClient]
  );

  const updateItem = useCallback(
    async (itemId: number, quantity: number) => {
      const newQuantity = Math.max(0, quantity);

      if (isAuthenticated) {
        if (newQuantity <= 0) {
          await removeMutation.mutateAsync(itemId);
        } else {
          await updateMutation.mutateAsync({ itemId, quantity: newQuantity });
        }
        queryClient.invalidateQueries({ queryKey: ["cart"] });
      } else {
        const items = getGuestCart();
        const existing = items.find((item) => item.product.id === itemId);

        if (!existing) return;

        if (newQuantity <= 0) {
          const filtered = items.filter((item) => item.product.id !== itemId);
          setGuestCart(filtered);
          setGuestCartState(filtered);
          return;
        }

        if (newQuantity > existing.product.stock_quantity) {
          toastStockLimitReached(existing.product.stock_quantity);
          throw new Error("Stock limit reached");
        }

        existing.quantity = newQuantity;
        setGuestCart(items);
        setGuestCartState([...items]);
      }
    },
    [isAuthenticated, updateMutation, removeMutation, queryClient]
  );

  const removeItem = useCallback(
    async (itemId: number) => {
      if (isAuthenticated) {
        await removeMutation.mutateAsync(itemId);
        queryClient.invalidateQueries({ queryKey: ["cart"] });
      } else {
        const items = getGuestCart().filter((item) => item.product.id !== itemId);
        setGuestCart(items);
        setGuestCartState(items);
      }
    },
    [isAuthenticated, removeMutation, queryClient]
  );

  const clear = useCallback(async () => {
    if (isAuthenticated) {
      await clearMutation.mutateAsync();
      queryClient.invalidateQueries({ queryKey: ["cart"] });
    } else {
      setGuestCart([]);
      setGuestCartState([]);
    }
  }, [isAuthenticated, clearMutation, queryClient]);

  const cart: Cart | null = isAuthenticated ? serverCart ?? null : buildGuestCart(guestCart);

  const value: CartContextValue = {
    cart,
    itemCount: cart?.item_count ?? 0,
    isLoading: isAuthenticated ? isLoading : false,
    addItem,
    updateItem,
    removeItem,
    clear,
    mergeGuestCart,
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
