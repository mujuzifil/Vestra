import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  getProductReviews,
  getMyReviews,
  submitReview,
  updateReview,
  deleteReview,
  voteReview,
  reportReview,
} from "@/lib/api/reviews";
import type { ReviewFormData, ReviewListResponse } from "@/lib/api/reviews";

const REVIEWS_KEY = "reviews";
const MY_REVIEWS_KEY = "my-reviews";

export function useProductReviews(slug: string, page = 1) {
  return useQuery<ReviewListResponse, Error>({
    queryKey: [REVIEWS_KEY, slug, page],
    queryFn: () => getProductReviews(slug, page),
    enabled: !!slug,
  });
}

export function useMyReviews(page = 1) {
  return useQuery({
    queryKey: [MY_REVIEWS_KEY, page],
    queryFn: () => getMyReviews(page),
    enabled: typeof window !== "undefined",
  });
}

export function useSubmitReview() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: submitReview,
    onSuccess: (_data, variables) => {
      queryClient.invalidateQueries({ queryKey: [REVIEWS_KEY] });
      queryClient.invalidateQueries({ queryKey: [MY_REVIEWS_KEY] });
      if ("product_id" in variables && variables.product_id) {
        queryClient.invalidateQueries({ queryKey: ["product", variables.product_id] });
      }
    },
  });
}

export function useUpdateReview() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: ReviewFormData }) => updateReview(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [REVIEWS_KEY] });
      queryClient.invalidateQueries({ queryKey: [MY_REVIEWS_KEY] });
    },
  });
}

export function useDeleteReview() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: deleteReview,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [REVIEWS_KEY] });
      queryClient.invalidateQueries({ queryKey: [MY_REVIEWS_KEY] });
    },
  });
}

export function useVoteReview() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ id, isHelpful }: { id: number; isHelpful: boolean }) => voteReview(id, isHelpful),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [REVIEWS_KEY] });
      queryClient.invalidateQueries({ queryKey: [MY_REVIEWS_KEY] });
    },
  });
}

export function useReportReview() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ id, reason, details }: { id: number; reason: string; details?: string }) =>
      reportReview(id, reason, details),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [REVIEWS_KEY] });
      queryClient.invalidateQueries({ queryKey: [MY_REVIEWS_KEY] });
    },
  });
}
