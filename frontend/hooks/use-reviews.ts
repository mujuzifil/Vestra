import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { getProductReviews, submitReview } from "@/lib/api/reviews";
import type { ReviewListResponse } from "@/lib/api/reviews";

const REVIEWS_KEY = "reviews";

export function useProductReviews(slug: string, page = 1) {
  return useQuery<ReviewListResponse, Error>({
    queryKey: [REVIEWS_KEY, slug, page],
    queryFn: () => getProductReviews(slug, page),
    enabled: !!slug,
  });
}

export function useSubmitReview() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: submitReview,
    onSuccess: (_, variables) => {
      // Invalidate reviews for the product
      queryClient.invalidateQueries({ queryKey: [REVIEWS_KEY] });
    },
  });
}
