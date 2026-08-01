import { apiPostFormData } from "./client";
import type { ApiResponse, QuoteRequest, QuoteRequestFormData, QuoteRequestItem } from "@/types";

function appendItems(formData: FormData, items: QuoteRequestItem[]) {
  items.forEach((item, index) => {
    if (item.product_id) {
      formData.append(`items[${index}][product_id]`, String(item.product_id));
    }
    formData.append(`items[${index}][product_name]`, item.product_name);
    if (item.package_size) {
      formData.append(`items[${index}][package_size]`, item.package_size);
    }
    formData.append(`items[${index}][quantity]`, String(item.quantity));
    if (item.notes) {
      formData.append(`items[${index}][notes]`, item.notes);
    }
  });
}

export async function createQuoteRequest(data: QuoteRequestFormData): Promise<QuoteRequest> {
  const formData = new FormData();

  formData.append("full_name", data.full_name);
  formData.append("company_name", data.company_name);
  formData.append("email", data.email);
  formData.append("phone", data.phone);

  if (data.district) formData.append("district", data.district);
  if (data.city) formData.append("city", data.city);
  if (data.address) formData.append("address", data.address);
  if (data.preferred_delivery_date) formData.append("preferred_delivery_date", data.preferred_delivery_date);
  if (data.delivery_location) formData.append("delivery_location", data.delivery_location);
  if (data.requirements) formData.append("requirements", data.requirements);

  if (data.items && data.items.length > 0) {
    appendItems(formData, data.items);
  }

  if (data.attachments) {
    Array.from(data.attachments).forEach((file) => {
      formData.append("attachments[]", file);
    });
  }

  const response = await apiPostFormData<ApiResponse<QuoteRequest>>("/quote-requests", formData);
  return response.data;
}
