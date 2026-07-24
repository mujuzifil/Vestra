"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  getDistributorQuotes,
  getDistributorQuote,
  createDistributorQuote,
  updateDistributorQuote,
  deleteDistributorQuote,
  submitDistributorQuote,
  acceptDistributorQuote,
  type CreateQuoteData,
} from "@/lib/api/distributor-portal";
import type { DistributorQuotation } from "@/types";

const KEY = "distributor-quotes";

export function useDistributorQuotes() {
  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery<DistributorQuotation[], Error>({
    queryKey: [KEY],
    queryFn: getDistributorQuotes,
  });

  const createMutation = useMutation({
    mutationFn: createDistributorQuote,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: [KEY] }),
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: Partial<DistributorQuotation> }) => updateDistributorQuote(id, data),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: [KEY] }),
  });

  const deleteMutation = useMutation({
    mutationFn: deleteDistributorQuote,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: [KEY] }),
  });

  const submitMutation = useMutation({
    mutationFn: submitDistributorQuote,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: [KEY] }),
  });

  const acceptMutation = useMutation({
    mutationFn: acceptDistributorQuote,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: [KEY] }),
  });

  return {
    data,
    isLoading,
    error,
    create: createMutation.mutateAsync,
    update: updateMutation.mutateAsync,
    remove: deleteMutation.mutateAsync,
    submit: submitMutation.mutateAsync,
    accept: acceptMutation.mutateAsync,
  };
}

export function useDistributorQuote(id: number) {
  const { data, isLoading, error } = useQuery<DistributorQuotation, Error>({
    queryKey: ["distributor-quote", id],
    queryFn: () => getDistributorQuote(id),
    enabled: !!id,
  });

  return { data, isLoading, error };
}

export type { CreateQuoteData };
export {
  getDistributorQuotes,
  getDistributorQuote,
  createDistributorQuote,
  updateDistributorQuote,
  deleteDistributorQuote,
  submitDistributorQuote,
  acceptDistributorQuote,
};
