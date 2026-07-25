import { apiGet } from "@/lib/api/client";
import type { ApiResponse, Product } from "@/types";

export async function getProducts(): Promise<Product[]> {
  const response = await apiGet<ApiResponse<{ data: Product[] }>>("/products?per_page=100");
  return response.data.data;
}

export async function getProductBySlug(slug: string): Promise<Product> {
  const response = await apiGet<ApiResponse<Product>>(`/products/${encodeURIComponent(slug)}`);
  return response.data;
}
