import { apiGet } from "./client";
import type { ApiResponse, PaginatedResponse, CustomerDocument } from "@/types";

export async function getAccountDocuments(page: number = 1): Promise<PaginatedResponse<CustomerDocument>> {
  const response = await apiGet<ApiResponse<PaginatedResponse<CustomerDocument>>>(`/account/documents?page=${page}`);
  return response.data;
}

export function getAccountDocumentDownloadUrl(id: number): string {
  const base = process.env.NEXT_PUBLIC_API_URL?.replace(/\/+$/, "") ?? "http://localhost:8000/api/v1";
  return `${base}/account/documents/${id}/download`;
}
