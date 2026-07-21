import { apiGet, apiPost } from "./client";
import type { Order, ApiResponse } from "@/types";

export interface CheckoutData {
  payment_method: string;
  shipping_address: {
    full_name: string;
    phone: string;
    city: string;
    region?: string;
    district?: string;
    address_line: string;
  };
  shipping_cost?: number;
  tax_amount?: number;
  notes?: string;
}

export async function getOrders(): Promise<Order[]> {
  const response = await apiGet<ApiResponse<Order[]>>("/orders");
  return response.data;
}

export async function getOrder(id: number): Promise<Order> {
  const response = await apiGet<ApiResponse<Order>>(`/orders/${id}`);
  return response.data;
}

export async function checkout(data: CheckoutData): Promise<Order> {
  const response = await apiPost<ApiResponse<Order>, Record<string, unknown>>("/checkout", data as unknown as Record<string, unknown>);
  return response.data;
}
