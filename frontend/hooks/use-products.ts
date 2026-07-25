import { useQuery } from "@tanstack/react-query";
import { getAllProducts, getProductBySlug, getProducts, getSearchSuggestions, getPopularSearches } from "@/lib/api/products";
import type { ProductFilters, ProductsResponse } from "@/lib/api/products";
import type { Product } from "@/types";

const PRODUCTS_KEY = "products";
const SEARCH_KEY = "product-search";

export function useProducts() {
  return useQuery<Product[], Error>({
    queryKey: [PRODUCTS_KEY],
    queryFn: getAllProducts,
  });
}

export function useProductSearch(filters: ProductFilters = {}, enabled = true) {
  return useQuery<ProductsResponse, Error>({
    queryKey: [SEARCH_KEY, filters],
    queryFn: () => getProducts(filters),
    enabled,
  });
}

export function useProduct(slug: string) {
  return useQuery<Product | null, Error>({
    queryKey: [PRODUCTS_KEY, slug],
    queryFn: () => getProductBySlug(slug),
    enabled: !!slug,
  });
}

export function useSearchSuggestions(query: string) {
  return useQuery({
    queryKey: ["search-suggestions", query],
    queryFn: () => getSearchSuggestions(query),
    enabled: query.length >= 2,
  });
}

export function usePopularSearches(limit = 6) {
  return useQuery({
    queryKey: ["popular-searches", limit],
    queryFn: () => getPopularSearches(limit),
  });
}
