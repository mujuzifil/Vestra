import { apiPost } from "./client";
import type { ContactFormData, ApiResponse } from "@/types";

interface ContactMessage {
  id: number;
  name: string;
  email: string;
  subject: string;
  message: string;
  created_at: string;
}

export async function submitContact(
  data: ContactFormData
): Promise<ContactMessage> {
  const response = await apiPost<ApiResponse<ContactMessage>, ContactFormData>(
    "/contact",
    data
  );
  return response.data;
}
