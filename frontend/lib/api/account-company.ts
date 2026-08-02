import { apiGet, apiPut } from "./client";
import type { ApiResponse, CompanyProfile } from "@/types";

export async function getCompanyProfile(): Promise<CompanyProfile> {
  const response = await apiGet<ApiResponse<CompanyProfile>>("/account/company");
  return response.data;
}

export async function updateCompanyProfile(data: Partial<CompanyProfile>): Promise<CompanyProfile> {
  const response = await apiPut<ApiResponse<CompanyProfile>, Partial<CompanyProfile>>("/account/company", data);
  return response.data;
}
