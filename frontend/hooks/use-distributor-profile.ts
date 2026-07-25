"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  getDistributorProfile,
  updateDistributorProfile,
  uploadDistributorLogo,
  removeDistributorLogo,
} from "@/lib/api/distributor-portal";
import type { Distributor } from "@/types";

const KEY = "distributor-profile";

export function useDistributorProfile() {
  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery<Distributor, Error>({
    queryKey: [KEY],
    queryFn: getDistributorProfile,
  });

  const updateMutation = useMutation({
    mutationFn: updateDistributorProfile,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [KEY] });
      queryClient.invalidateQueries({ queryKey: ["distributor-dashboard"] });
    },
  });

  const uploadLogoMutation = useMutation({
    mutationFn: uploadDistributorLogo,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [KEY] });
      queryClient.invalidateQueries({ queryKey: ["distributor-dashboard"] });
    },
  });

  const removeLogoMutation = useMutation({
    mutationFn: removeDistributorLogo,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [KEY] });
      queryClient.invalidateQueries({ queryKey: ["distributor-dashboard"] });
    },
  });

  return {
    data,
    isLoading,
    error,
    update: updateMutation.mutateAsync,
    uploadLogo: uploadLogoMutation.mutateAsync,
    removeLogo: removeLogoMutation.mutateAsync,
  };
}
