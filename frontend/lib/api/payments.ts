import { apiGet, apiPost } from "./client";
import type { ApiResponse } from "@/types";

export interface PaymentInitiation {
  payment_link: string;
  transaction_reference: string;
}

export interface PaymentTransaction {
  status: string;
  amount: number;
  paid_at: string | null;
  order_id?: number;
}

export async function initiatePayment(orderId: number): Promise<PaymentInitiation> {
  const response = await apiPost<ApiResponse<PaymentInitiation>>("/payments/initiate", {
    order_id: orderId,
  });
  return response.data;
}

export async function verifyPayment(reference: string): Promise<ApiResponse<null>> {
  return apiGet<ApiResponse<null>>(`/payments/${encodeURIComponent(reference)}/verify`);
}

export async function getTransaction(reference: string): Promise<PaymentTransaction> {
  const response = await apiGet<ApiResponse<PaymentTransaction>>(
    `/payments/${encodeURIComponent(reference)}`
  );
  return response.data;
}
