import { apiGet } from "./client";
import type { Category, ApiResponse } from "@/types";

export async function getCategories(): Promise<Category[]> {
  const response = await apiGet<ApiResponse<Category[]>>("/categories");
  return response.data;
}
