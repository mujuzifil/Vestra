import { apiGet } from "@/lib/api/client";
import type { ApiResponse, Category } from "@/types";

export async function getCategories(): Promise<Category[]> {
  const response = await apiGet<ApiResponse<Category[]>>("/categories");
  return response.data;
}

