"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  getAddresses,
  createAddress,
  updateAddress,
  deleteAddress,
  type CreateAddressData,
} from "@/lib/api/auth";
import type { Address } from "@/types";

const ADDRESSES_KEY = "addresses";

export function useAddresses() {
  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery<Address[], Error>({
    queryKey: [ADDRESSES_KEY],
    queryFn: getAddresses,
  });

  const defaultShipping = data?.find((a) => a.is_default_shipping) || data?.find((a) => a.is_default) || null;
  const defaultBilling = data?.find((a) => a.is_default_billing) || data?.find((a) => a.is_default) || null;

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
    defaultShipping,
    defaultBilling,
    create: createMutation.mutateAsync,
    update: updateMutation.mutateAsync,
    remove: deleteMutation.mutateAsync,
  };
}

export type { CreateAddressData };
export { getAddresses, createAddress, updateAddress, deleteAddress };
