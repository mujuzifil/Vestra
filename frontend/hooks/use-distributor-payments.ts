"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { getDistributorPayments, createDistributorPayment, type CreatePaymentUploadData } from "@/lib/api/distributor-portal";
import type { DistributorPaymentUpload } from "@/types";

const KEY = "distributor-payments";

export function useDistributorPayments() {
  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery<DistributorPaymentUpload[], Error>({
    queryKey: [KEY],
    queryFn: getDistributorPayments,
  });

  const createMutation = useMutation({
    mutationFn: createDistributorPayment,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: [KEY] }),
  });

  return {
    data,
    isLoading,
    error,
    create: createMutation.mutateAsync,
  };
}

export type { CreatePaymentUploadData };
export { getDistributorPayments, createDistributorPayment };
