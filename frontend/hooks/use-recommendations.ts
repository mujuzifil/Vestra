import { useQuery } from "@tanstack/react-query";
import { getHomeRecommendations, getProductRecommendations } from "@/lib/api/recommendations";
import type { HomeRecommendations, ProductRecommendations } from "@/lib/api/recommendations";

const KEY = "recommendations";

export function useHomeRecommendations(limit = 6) {
  return useQuery<HomeRecommendations, Error>({
    queryKey: [KEY, "home", limit],
    queryFn: () => getHomeRecommendations(limit),
  });
}

export function useProductRecommendations(slug: string, limit = 4) {
  return useQuery<ProductRecommendations, Error>({
    queryKey: [KEY, "product", slug, limit],
    queryFn: () => getProductRecommendations(slug, limit),
    enabled: !!slug,
  });
}
