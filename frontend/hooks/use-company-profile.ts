"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { getCompanyProfile, updateCompanyProfile } from "@/lib/api/account-company";
import type { CompanyProfile } from "@/types";

const KEY = ["account", "company"];

export function useCompanyProfile() {
  return useQuery<CompanyProfile, Error>({
    queryKey: KEY,
    queryFn: getCompanyProfile,
    enabled: typeof window !== "undefined",
  });
}

export function useUpdateCompanyProfile() {
  const queryClient = useQueryClient();

  return useMutation<CompanyProfile, Error, Partial<CompanyProfile>>({
    mutationFn: updateCompanyProfile,
    onSuccess: (updated) => {
      queryClient.setQueryData(KEY, updated);
    },
  });
}
