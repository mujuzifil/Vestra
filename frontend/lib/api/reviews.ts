import { apiDelete, apiGet, apiPost, apiPostFormData, apiPutFormData } from "./client";
import type { ApiResponse, PaginatedResponse } from "@/types";

export interface ReviewUser {
  id?: number;
  name: string;
}

export interface ReviewProduct {
  id: number;
  name: string;
  slug: string;
  image: string | null;
}

export interface ReviewImage {
  id: number;
  url: string;
  sort_order: number;
}

export interface ReviewAdminReply {
  content: string;
  replied_at: string;
  replied_by: string;
}

export interface Review {
  id: number;
  user: ReviewUser;
  product?: ReviewProduct;
  rating: number;
  title: string | null;
  comment: string | null;
  pros: string[];
  cons: string[];
  images: ReviewImage[];
  status: string;
  is_featured: boolean;
  is_pinned: boolean;
  is_hidden: boolean;
  helpful_count: number;
  user_vote: boolean | null;
  reported_count: number;
  user_reported: boolean;
  admin_reply?: ReviewAdminReply;
  created_at: string;
  updated_at: string;
}

export interface ReviewFormData {
  product_id: number;
  rating: number;
  title?: string;
  comment?: string;
  pros?: string[];
  cons?: string[];
  images?: File[];
}

export interface ReviewListResponse {
  reviews: Review[];
  average_rating: number;
  review_count: number;
  rating_distribution: { rating: number; count: number }[];
  pagination: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

function buildReviewFormData(data: ReviewFormData): FormData {
  const formData = new FormData();
  formData.append("product_id", String(data.product_id));
  formData.append("rating", String(data.rating));
  if (data.title) formData.append("title", data.title);
  if (data.comment) formData.append("comment", data.comment);
  (data.pros ?? []).forEach((pro) => formData.append("pros[]", pro));
  (data.cons ?? []).forEach((con) => formData.append("cons[]", con));
  (data.images ?? []).forEach((file) => formData.append("images[]", file));
  return formData;
}

export async function getProductReviews(slug: string, page = 1): Promise<ReviewListResponse> {
  const response = await apiGet<ApiResponse<ReviewListResponse>>(`/products/${slug}/reviews?page=${page}`);
  return response.data;
}

export async function getMyReviews(page = 1): Promise<PaginatedResponse<Review>> {
  const response = await apiGet<ApiResponse<PaginatedResponse<Review>>>(`/auth/reviews?page=${page}`);
  return response.data;
}

export async function submitReview(data: ReviewFormData): Promise<Review> {
  const response = await apiPostFormData<ApiResponse<Review>>("/reviews", buildReviewFormData(data));
  return response.data;
}

export async function updateReview(id: number, data: ReviewFormData): Promise<Review> {
  const response = await apiPutFormData<ApiResponse<Review>>(`/reviews/${id}`, buildReviewFormData(data));
  return response.data;
}

export async function deleteReview(id: number): Promise<void> {
  await apiDelete<ApiResponse<unknown>>(`/reviews/${id}`);
}

export async function voteReview(id: number, isHelpful: boolean): Promise<{ helpful_count: number; user_vote: boolean }> {
  const response = await apiPost<ApiResponse<{ helpful_count: number; user_vote: boolean }>>(`/reviews/${id}/helpful`, {
    is_helpful: isHelpful,
  });
  return response.data;
}

export async function reportReview(id: number, reason: string, details?: string): Promise<void> {
  await apiPost<ApiResponse<unknown>>(`/reviews/${id}/report`, { reason, details });
}
