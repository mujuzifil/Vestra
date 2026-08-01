import { apiGet } from "./client";
import type { Order, ApiResponse } from "@/types";

export async function getOrders(): Promise<Order[]> {
  const response = await apiGet<ApiResponse<Order[]>>("/orders");
  return response.data;
}

export async function getOrder(id: number): Promise<Order> {
  const response = await apiGet<ApiResponse<Order>>(`/orders/${id}`);
  return response.data;
}
