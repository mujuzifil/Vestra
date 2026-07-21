import { apiGet, apiPost, apiPut, apiDelete } from "./client";
import type { Cart, ApiResponse } from "@/types";

export async function getCart(): Promise<Cart> {
  const response = await apiGet<ApiResponse<Cart>>("/cart");
  return response.data;
}

export async function addToCart(productId: number, quantity: number = 1): Promise<Cart> {
  const response = await apiPost<ApiResponse<Cart>>("/cart/items", { product_id: productId, quantity });
  return response.data;
}

export async function updateCartItem(itemId: number, quantity: number): Promise<Cart> {
  const response = await apiPut<ApiResponse<Cart>>(`/cart/items/${itemId}`, { quantity });
  return response.data;
}

export async function removeCartItem(itemId: number): Promise<Cart> {
  const response = await apiDelete<ApiResponse<Cart>>(`/cart/items/${itemId}`);
  return response.data;
}

export async function clearCart(): Promise<Cart> {
  const response = await apiDelete<ApiResponse<Cart>>("/cart");
  return response.data;
}

export async function mergeCart(items: { product_id: number; quantity: number }[]): Promise<Cart> {
  const response = await apiPost<ApiResponse<Cart>>("/cart/merge", { items });
  return response.data;
}
