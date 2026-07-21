import { apiGet, apiPost, apiPut, apiDelete } from "./client";
import type { Customer, ApiResponse, Address, ContactFormData, AuthResponse } from "@/types";

export async function register(name: string, email: string, password: string, phone?: string): Promise<AuthResponse> {
  const response = await apiPost<ApiResponse<AuthResponse>>("/auth/register", {
    name,
    email,
    password,
    password_confirmation: password,
    phone,
  });
  return response.data;
}

export async function login(email: string, password: string): Promise<AuthResponse> {
  const response = await apiPost<ApiResponse<AuthResponse>>("/auth/login", { email, password });
  return response.data;
}

export async function logout(): Promise<void> {
  await apiPost<ApiResponse<null>>("/auth/logout", {});
}

export async function getProfile(): Promise<Customer> {
  const response = await apiGet<ApiResponse<Customer>>("/auth/profile");
  return response.data;
}

export async function updateProfile(data: { name?: string; phone?: string }): Promise<Customer> {
  const response = await apiPut<ApiResponse<Customer>>("/auth/profile", data);
  return response.data;
}

export async function changePassword(currentPassword: string, password: string): Promise<void> {
  await apiPost<ApiResponse<null>>("/auth/change-password", {
    current_password: currentPassword,
    password,
    password_confirmation: password,
  });
}

export async function getAddresses(): Promise<Address[]> {
  const response = await apiGet<ApiResponse<Address[]>>("/auth/addresses");
  return response.data;
}

export async function createAddress(data: Omit<Address, "id" | "created_at" | "updated_at">): Promise<Address> {
  const response = await apiPost<ApiResponse<Address>>("/auth/addresses", data);
  return response.data;
}

export async function updateAddress(id: number, data: Partial<Address>): Promise<Address> {
  const response = await apiPut<ApiResponse<Address>>(`/auth/addresses/${id}`, data);
  return response.data;
}

export async function deleteAddress(id: number): Promise<void> {
  await apiDelete<ApiResponse<null>>(`/auth/addresses/${id}`);
}
