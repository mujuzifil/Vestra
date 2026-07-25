import { apiDelete, apiGet, apiPost } from "./client";
import type { ApiResponse, Product } from "@/types";

export interface RecentlyViewedItem {
  id: number;
  viewed_at: string;
  product: Product;
}

export interface RecentlyViewedResponse {
  items: RecentlyViewedItem[];
  pagination: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export async function getRecentlyViewed(page = 1): Promise<RecentlyViewedResponse> {
  const response = await apiGet<ApiResponse<RecentlyViewedResponse>>(`/auth/recently-viewed?page=${page}`);
  return response.data;
}

export async function recordProductView(productId: number): Promise<void> {
  await apiPost<ApiResponse<unknown>>("/auth/recently-viewed", { product_id: productId });
}

export async function removeRecentlyViewed(productId: number): Promise<void> {
  await apiDelete<ApiResponse<unknown>>(`/auth/recently-viewed/${productId}`);
}

export async function clearRecentlyViewed(): Promise<void> {
  await apiDelete<ApiResponse<unknown>>("/auth/recently-viewed");
}
