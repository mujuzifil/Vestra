"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { getAddresses, createAddress, updateAddress, deleteAddress } from "@/lib/api/auth";
import type { Address } from "@/types";

const ADDRESSES_KEY = "addresses";

export function useAddresses() {
  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery<Address[], Error>({
    queryKey: [ADDRESSES_KEY],
    queryFn: getAddresses,
  });

  const createMutation = useMutation({
    mutationFn: createAddress,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [ADDRESSES_KEY] });
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: Partial<Address> }) => updateAddress(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [ADDRESSES_KEY] });
    },
  });

  const deleteMutation = useMutation({
    mutationFn: deleteAddress,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [ADDRESSES_KEY] });
    },
  });

  return {
    data,
    isLoading,
    error,
    create: createMutation.mutateAsync,
    update: updateMutation.mutateAsync,
    remove: deleteMutation.mutateAsync,
  };
}
