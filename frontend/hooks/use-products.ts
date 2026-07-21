"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { getProducts, getProductBySlug } from "@/lib/api/products";
import type { Product } from "@/types";

const PRODUCTS_KEY = "products";

export function useProducts() {
  return useQuery<Product[], Error>({
    queryKey: [PRODUCTS_KEY],
    queryFn: getProducts,
  });
}

export function useProduct(slug: string) {
  return useQuery<Product | null, Error>({
    queryKey: [PRODUCTS_KEY, slug],
    queryFn: () => getProductBySlug(slug),
    enabled: !!slug,
  });
}
