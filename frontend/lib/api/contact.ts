import { apiPostFormData } from "./client";
import type { ApiResponse, ContactFormData } from "@/types";

interface ContactMessage {
  id: number;
  name: string;
  company: string | null;
  email: string;
  phone: string | null;
  subject: string;
  enquiry_type: string;
  enquiry_type_label: string;
  message: string;
  status: string;
  status_label: string;
  created_at: string;
}

export async function submitContact(data: ContactFormData): Promise<ContactMessage> {
  const formData = new FormData();

  formData.append("name", data.name);
  formData.append("email", data.email);
  formData.append("subject", data.subject);
  formData.append("enquiry_type", data.enquiry_type);
  formData.append("message", data.message);

  if (data.company) formData.append("company", data.company);
  if (data.phone) formData.append("phone", data.phone);

  if (data.attachments) {
    Array.from(data.attachments).forEach((file) => {
      formData.append("attachments[]", file);
    });
  }

  const response = await apiPostFormData<ApiResponse<ContactMessage>>("/contact", formData);
  return response.data;
}
