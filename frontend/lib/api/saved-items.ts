import { apiDelete, apiGet, apiPost } from "./client";
import type { ApiResponse, Product } from "@/types";

export interface SavedItem {
  id: number;
  product: Product;
  created_at: string;
}

export interface SavedItemsResponse {
  items: SavedItem[];
  pagination: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export async function getSavedItems(page = 1): Promise<SavedItemsResponse> {
  const response = await apiGet<ApiResponse<SavedItemsResponse>>(`/auth/saved-for-later?page=${page}`);
  return response.data;
}

export async function addToSavedItems(productId: number): Promise<SavedItem> {
  const response = await apiPost<ApiResponse<SavedItem>>("/auth/saved-for-later", { product_id: productId });
  return response.data;
}

export async function removeFromSavedItems(productId: number): Promise<void> {
  await apiDelete<ApiResponse<unknown>>(`/auth/saved-for-later/${productId}`);
}

export async function mergeSavedItems(items: { product_id: number }[]): Promise<void> {
  await apiPost<ApiResponse<unknown>>("/auth/saved-for-later/merge", { items });
}
