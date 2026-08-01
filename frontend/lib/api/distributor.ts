import { apiPostFormData } from "./client";
import type { DistributorFormData, ApiResponse } from "@/types";

interface DistributorRequest {
  id: number;
  company_name: string;
  contact_person: string;
  email: string;
  phone: string;
  business_type: string;
  created_at: string;
}

export async function submitDistributor(
  data: DistributorFormData
): Promise<DistributorRequest> {
  const payload = new FormData();

  payload.append("company_name", data.businessName);
  payload.append("contact_person", data.fullName);
  payload.append("position", data.position);
  payload.append("email", data.email);
  payload.append("phone", data.phone);
  payload.append("district", data.district);
  payload.append("physical_address", data.physicalAddress);
  payload.append("years_in_business", data.yearsInBusiness);
  payload.append("business_type", data.businessType);
  payload.append("regions_covered", data.regionsCovered);
  payload.append("existing_brands", data.existingBrands);
  payload.append("warehouse_availability", data.warehouseAvailability);
  payload.append("delivery_capability", data.deliveryCapability);
  payload.append("additional_information", data.additionalInformation);

  if (data.documents) {
    Array.from(data.documents).forEach((file) => {
      payload.append("documents[]", file);
    });
  }

  const response = await apiPostFormData<ApiResponse<DistributorRequest>>(
    "/distributor",
    payload
  );
  return response.data;
}
