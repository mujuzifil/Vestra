"use client";

import { createContext, useContext, useState, useCallback, useEffect, type ReactNode } from "react";
import { toastError, toastSuccess } from "@/lib/toast-utils";
import type { Product } from "@/types";

const MAX_COMPARE = 4;
const STORAGE_KEY = "vestra_compare";

interface CompareContextValue {
  items: Product[];
  addToCompare: (product: Product) => void;
  removeFromCompare: (productId: number) => void;
  clearCompare: () => void;
  isInCompare: (productId: number) => boolean;
  canAddMore: boolean;
}

const CompareContext = createContext<CompareContextValue | null>(null);

function loadItems(): Product[] {
  if (typeof window === "undefined") return [];
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    const parsed = raw ? JSON.parse(raw) : [];
    return Array.isArray(parsed) ? parsed : [];
  } catch {
    return [];
  }
}

function saveItems(items: Product[]) {
  if (typeof window === "undefined") return;
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
  } catch {
    toastError("Could not save comparison list. Storage may be full.");
  }
}

export function CompareProvider({ children }: { children: ReactNode }) {
  const [items, setItems] = useState<Product[]>(loadItems);

  useEffect(() => {
    saveItems(items);
  }, [items]);

  const addToCompare = useCallback((product: Product) => {
    setItems((prev) => {
      if (prev.some((item) => item.id === product.id)) {
        toastError("This product is already in your comparison list.");
        return prev;
      }
      if (prev.length >= MAX_COMPARE) {
        toastError(`You can compare up to ${MAX_COMPARE} products.`);
        return prev;
      }
      toastSuccess("Added to comparison");
      return [...prev, product];
    });
  }, []);

  const removeFromCompare = useCallback((productId: number) => {
    setItems((prev) => prev.filter((item) => item.id !== productId));
  }, []);

  const clearCompare = useCallback(() => {
    setItems([]);
  }, []);

  const isInCompare = useCallback((productId: number) => items.some((item) => item.id === productId), [items]);
  const canAddMore = items.length < MAX_COMPARE;

  return (
    <CompareContext.Provider
      value={{ items, addToCompare, removeFromCompare, clearCompare, isInCompare, canAddMore }}
    >
      {children}
    </CompareContext.Provider>
  );
}

export function useCompare() {
  const context = useContext(CompareContext);
  if (!context) {
    throw new Error("useCompare must be used within a CompareProvider");
  }
  return context;
}
