import { apiPost } from "./client";
import type { ApiResponse, QuoteRequest, QuoteRequestFormData } from "@/types";

export async function createQuoteRequest(data: QuoteRequestFormData): Promise<QuoteRequest> {
  const response = await apiPost<ApiResponse<QuoteRequest>, QuoteRequestFormData>("/quote-requests", data);
  return response.data;
}
