import { apiGet, apiPostFormData } from "./client";
import type { ApiResponse, PaginatedResponse, SupportTicket } from "@/types";

export interface CreateSupportTicketData {
  subject: string;
  message: string;
  enquiry_type?: "general" | "sales" | "distributor" | "quote" | "technical_support" | "other";
  priority?: "low" | "medium" | "high" | "urgent";
  attachments?: FileList | null;
}

export async function getSupportTickets(page: number = 1): Promise<PaginatedResponse<SupportTicket>> {
  const response = await apiGet<ApiResponse<PaginatedResponse<SupportTicket>>>(`/account/support?page=${page}`);
  return response.data;
}

export async function getSupportTicket(id: number): Promise<SupportTicket> {
  const response = await apiGet<ApiResponse<SupportTicket>>(`/account/support/${id}`);
  return response.data;
}

export async function createSupportTicket(data: CreateSupportTicketData): Promise<SupportTicket> {
  const formData = new FormData();
  formData.append("subject", data.subject);
  formData.append("message", data.message);
  if (data.enquiry_type) formData.append("enquiry_type", data.enquiry_type);
  if (data.priority) formData.append("priority", data.priority);
  if (data.attachments) {
    Array.from(data.attachments).forEach((file) => {
      formData.append("attachments[]", file);
    });
  }

  const response = await apiPostFormData<ApiResponse<SupportTicket>>("/account/support", formData);
  return response.data;
}

export async function replyToSupportTicket(id: number, data: { message: string; attachments?: FileList | null }): Promise<SupportTicket> {
  const formData = new FormData();
  formData.append("message", data.message);
  if (data.attachments) {
    Array.from(data.attachments).forEach((file) => {
      formData.append("attachments[]", file);
    });
  }

  const response = await apiPostFormData<ApiResponse<SupportTicket>>(`/account/support/${id}/reply`, formData);
  return response.data;
}
