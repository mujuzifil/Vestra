import { apiPost } from "./client";
import type { DistributorFormData, ApiResponse } from "@/types";

interface DistributorRequest {
  id: number;
  full_name: string;
  business_name: string;
  email: string;
  phone: string;
  city: string;
  business_type: string;
  experience: string;
  created_at: string;
}

export async function submitDistributor(
  data: DistributorFormData
): Promise<DistributorRequest> {
  // Map camelCase frontend keys to snake_case backend keys
  const payload = {
    full_name: data.fullName,
    business_name: data.businessName,
    email: data.email,
    phone: data.phone,
    city: data.city,
    business_type: data.businessType,
    experience: data.experience,
  };

  const response = await apiPost<ApiResponse<DistributorRequest>, typeof payload>(
    "/distributor",
    payload
  );
  return response.data;
}
