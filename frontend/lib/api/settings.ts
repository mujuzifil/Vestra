import { apiGet } from "./client";
import type { Setting, ApiResponse } from "@/types";

export async function getSettings(): Promise<Setting[]> {
  const response = await apiGet<ApiResponse<Setting[]>>("/settings");
  return response.data;
}

export function getSettingValue(
  settings: Setting[],
  key: string,
  fallback = ""
): string {
  const setting = settings.find((s) => s.key === key);
  return setting?.value ?? fallback;
}

export function buildContactInfo(settings: Setting[]) {
  return {
    phone: getSettingValue(settings, "contact_phone", "+256 707 128 442"),
    email: getSettingValue(settings, "contact_email", "vestradetergent@gmail.com"),
    location: getSettingValue(settings, "contact_location", "Kampala, Uganda"),
    whatsapp: getSettingValue(settings, "contact_whatsapp", "+256 707 128 442"),
    businessHours: getSettingValue(settings, "contact_business_hours", "Mon - Fri: 8:00 AM - 6:00 PM"),
  };
}

export function buildCompanyInfo(settings: Setting[]) {
  return {
    name: getSettingValue(settings, "company_name", "VESTRA"),
    tagline: getSettingValue(settings, "company_tagline", "Professional Fabric Care"),
    description: getSettingValue(settings, "company_description", "VESTRA is a premium fabric care brand dedicated to developing high-performance cleaning solutions."),
    mission: getSettingValue(settings, "company_mission", "To deliver professional-grade fabric care solutions."),
    vision: getSettingValue(settings, "company_vision", "Building one of Africa's most respected fabric care brands."),
    philosophy: getSettingValue(settings, "company_philosophy", "We believe fabric care should do more than clean."),
    founded: getSettingValue(settings, "company_founded", "2020"),
    headquarters: getSettingValue(settings, "company_headquarters", "Kampala, Uganda"),
  };
}
