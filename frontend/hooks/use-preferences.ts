"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { getPreferences, updatePreferences } from "@/lib/api/auth";
import type { CustomerPreferences } from "@/types";

const PREFERENCES_KEY = ["auth", "preferences"];

export function usePreferences() {
  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery<CustomerPreferences, Error>({
    queryKey: PREFERENCES_KEY,
    queryFn: getPreferences,
  });

  const updateMutation = useMutation({
    mutationFn: (payload: Partial<CustomerPreferences>) => updatePreferences(payload),
    onSuccess: (updated) => {
      queryClient.setQueryData(PREFERENCES_KEY, updated);
    },
  });

  return {
    data,
    isLoading,
    error,
    update: updateMutation.mutateAsync,
    isUpdating: updateMutation.isPending,
  };
}
