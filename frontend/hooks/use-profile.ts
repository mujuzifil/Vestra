"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { getProfile, updateProfile, type UpdateProfileData } from "@/lib/api/auth";
import type { Customer } from "@/types";

const PROFILE_KEY = ["auth", "profile"];

export function useProfile() {
  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery<Customer, Error>({
    queryKey: PROFILE_KEY,
    queryFn: getProfile,
  });

  const updateMutation = useMutation({
    mutationFn: (payload: UpdateProfileData) => updateProfile(payload),
    onSuccess: (updated) => {
      queryClient.setQueryData(PROFILE_KEY, updated);
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
