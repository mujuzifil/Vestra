import { apiDelete, apiGet, apiPost } from "./client";
import type { ApiResponse, Product } from "@/types";

export interface WishlistItem {
  id: number;
  list_name: string;
  notes: string | null;
  product: Product;
  created_at: string;
}

export interface WishlistResponse {
  items: WishlistItem[];
  pagination: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export async function getWishlist(page = 1): Promise<WishlistResponse> {
  const response = await apiGet<ApiResponse<WishlistResponse>>(`/auth/wishlist?page=${page}`);
  return response.data;
}

export async function addToWishlist(productId: number, listName?: string, notes?: string): Promise<WishlistItem> {
  const response = await apiPost<ApiResponse<WishlistItem>>("/auth/wishlist", {
    product_id: productId,
    list_name: listName,
    notes,
  });
  return response.data;
}

export async function removeFromWishlist(productId: number): Promise<void> {
  await apiDelete<ApiResponse<unknown>>(`/auth/wishlist/${productId}`);
}

export async function moveWishlistToCart(productId: number): Promise<void> {
  await apiPost<ApiResponse<unknown>>(`/auth/wishlist/${productId}/move-to-cart`, {});
}

export async function mergeWishlist(items: { product_id: number; list_name?: string; notes?: string }[]): Promise<void> {
  await apiPost<ApiResponse<unknown>>("/auth/wishlist/merge", { items });
}
