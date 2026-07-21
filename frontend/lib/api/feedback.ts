import { apiPost } from "./client";
import type { ApiResponse } from "@/types";

export async function submitFeedback(data: {
  category: string;
  subject: string;
  message: string;
}): Promise<unknown> {
  const response = await apiPost<ApiResponse<unknown>>("/feedback", data);
  return response.data;
}
