import { apiGet } from "./client";
import type { Product, ApiResponse } from "@/types";

export interface ProductFilters {
  page?: number;
  per_page?: number;
  category?: string;
  search?: string;
  featured?: boolean;
  sort?: string;
  min_price?: number;
  max_price?: number;
  in_stock?: boolean;
  session_id?: string;
}

export interface ProductsResponse {
  data: Product[];
  links: {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
  };
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    links: { url: string | null; label: string; active: boolean }[];
    path: string;
    per_page: number;
    to: number;
    total: number;
  };
}

function buildQueryString(params: ProductFilters): string {
  const qs = new URLSearchParams();
  Object.entries(params).forEach(([key, value]) => {
    if (value === undefined || value === null || value === "") return;
    qs.append(key, String(value));
  });
  const query = qs.toString();
  return query ? `?${query}` : "";
}

export async function getProducts(filters: ProductFilters = {}): Promise<ProductsResponse> {
  const response = await apiGet<ApiResponse<ProductsResponse>>(`/products${buildQueryString(filters)}`);
  return response.data;
}

export async function getAllProducts(): Promise<Product[]> {
  const response = await apiGet<ApiResponse<ProductsResponse>>("/products?per_page=100");
  return response.data.data;
}

export async function getProductBySlug(slug: string): Promise<Product | null> {
  try {
    const response = await apiGet<ApiResponse<Product>>(`/products/${slug}`);
    return response.data;
  } catch (error) {
    if (error instanceof Error && error.message.includes("404")) {
      return null;
    }
    throw error;
  }
}

export async function getSearchSuggestions(query: string): Promise<{ id: number; name: string; slug: string; type: string }[]> {
  const response = await apiGet<ApiResponse<{ id: number; name: string; slug: string; type: string }[]>>(
    `/search/autocomplete?q=${encodeURIComponent(query)}`
  );
  return response.data;
}

export async function getPopularSearches(limit = 6): Promise<{ term: string; count: number }[]> {
  const response = await apiGet<ApiResponse<{ term: string; count: number }[]>>(`/search/popular?limit=${limit}`);
  return response.data;
}
