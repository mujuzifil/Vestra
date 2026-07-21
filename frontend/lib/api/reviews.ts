import { apiGet, apiPost } from "./client";
import type { ApiResponse } from "@/types";

export interface Review {
  id: number;
  user: { name: string };
  rating: number;
  title: string | null;
  comment: string | null;
  status: string;
  created_at: string;
}

export interface ReviewListResponse {
  reviews: Review[];
  average_rating: number;
  review_count: number;
  pagination: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export async function getProductReviews(slug: string, page = 1): Promise<ReviewListResponse> {
  const response = await apiGet<ApiResponse<ReviewListResponse>>(`/products/${slug}/reviews?page=${page}`);
  return response.data;
}

export async function submitReview(data: {
  product_id: number;
  rating: number;
  title?: string;
  comment?: string;
}): Promise<Review> {
  const response = await apiPost<ApiResponse<Review>>("/reviews", data);
  return response.data;
}
