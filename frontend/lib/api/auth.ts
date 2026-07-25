import { apiGet, apiPost, apiPut, apiDelete, apiUpload } from "./client";
import type { Customer, ApiResponse, Address, AuthResponse, CustomerPreferences, ActivityItem } from "@/types";

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

export interface UpdateProfileData {
  name?: string;
  first_name?: string | null;
  last_name?: string | null;
  email?: string;
  phone?: string | null;
  date_of_birth?: string | null;
  gender?: Customer["gender"] | null;
}

export async function updateProfile(data: UpdateProfileData): Promise<Customer> {
  const response = await apiPut<ApiResponse<Customer>>("/auth/profile", data as Record<string, unknown>);
  return response.data;
}

export async function uploadAvatar(file: File): Promise<Customer> {
  const response = await apiUpload<ApiResponse<Customer>>("/auth/avatar", file, "avatar");
  return response.data;
}

export async function deleteAvatar(): Promise<Customer> {
  const response = await apiDelete<ApiResponse<Customer>>("/auth/avatar");
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

export type CreateAddressData = Omit<Address, "id" | "created_at" | "updated_at">;

export async function createAddress(data: CreateAddressData): Promise<Address> {
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

export async function getPreferences(): Promise<CustomerPreferences> {
  const response = await apiGet<ApiResponse<CustomerPreferences>>("/auth/preferences");
  return response.data;
}

export async function updatePreferences(data: Partial<CustomerPreferences>): Promise<CustomerPreferences> {
  const response = await apiPut<ApiResponse<CustomerPreferences>>("/auth/preferences", data);
  return response.data;
}

export interface ActivityResponse {
  data: ActivityItem[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export async function getActivity(page: number = 1): Promise<ActivityResponse> {
  const response = await apiGet<ApiResponse<ActivityResponse>>(`/auth/activity?page=${page}`);
  return response.data;
}

export async function requestAccountDeletion(reason?: string, password?: string): Promise<void> {
  await apiPost<ApiResponse<null>>("/auth/account-deletion-request", { reason, password });
}
