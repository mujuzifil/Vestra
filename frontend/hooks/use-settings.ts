"use client";

import { useQuery } from "@tanstack/react-query";
import { getSettings, getSettingValue, buildContactInfo, buildCompanyInfo } from "@/lib/api/settings";
import type { Setting } from "@/types";

const SETTINGS_KEY = "settings";

export function useSettings() {
  return useQuery<Setting[], Error>({
    queryKey: [SETTINGS_KEY],
    queryFn: getSettings,
  });
}

export function useSetting(key: string, fallback = "") {
  const { data: settings } = useSettings();
  return settings ? getSettingValue(settings, key, fallback) : fallback;
}

export function useContactInfo() {
  const { data: settings, isLoading, error } = useSettings();
  return {
    contactInfo: settings ? buildContactInfo(settings) : null,
    isLoading,
    error,
  };
}

export function useCompanyInfo() {
  const { data: settings, isLoading, error } = useSettings();
  return {
    companyInfo: settings ? buildCompanyInfo(settings) : null,
    isLoading,
    error,
  };
}
