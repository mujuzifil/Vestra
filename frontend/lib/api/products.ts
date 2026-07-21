import { apiGet } from "./client";
import type { Product, ApiResponse } from "@/types";

export async function getProducts(): Promise<Product[]> {
  const response = await apiGet<ApiResponse<Product[]>>("/products");
  return response.data;
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
