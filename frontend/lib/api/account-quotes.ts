import { apiGet } from "./client";
import type { ApiResponse, PaginatedResponse, CustomerQuote } from "@/types";

export async function getAccountQuotes(page: number = 1): Promise<PaginatedResponse<CustomerQuote>> {
  const response = await apiGet<ApiResponse<PaginatedResponse<CustomerQuote>>>(`/account/quotes?page=${page}`);
  return response.data;
}

export async function getAccountQuote(id: number): Promise<CustomerQuote> {
  const response = await apiGet<ApiResponse<CustomerQuote>>(`/account/quotes/${id}`);
  return response.data;
}
