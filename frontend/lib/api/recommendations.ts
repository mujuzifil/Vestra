import { apiGet } from "./client";
import type { ApiResponse, Product } from "@/types";

export interface HomeRecommendations {
  best_sellers: Product[];
  new_arrivals: Product[];
  trending: Product[];
  recently_viewed: Product[];
}

export interface ProductRecommendations {
  related: Product[];
  frequently_bought_together: Product[];
}

export async function getHomeRecommendations(limit = 6): Promise<HomeRecommendations> {
  const response = await apiGet<ApiResponse<HomeRecommendations>>(`/recommendations?limit=${limit}`);
  return response.data;
}

export async function getProductRecommendations(slug: string, limit = 4): Promise<ProductRecommendations> {
  const response = await apiGet<ApiResponse<ProductRecommendations>>(`/products/${slug}/recommendations?limit=${limit}`);
  return response.data;
}
