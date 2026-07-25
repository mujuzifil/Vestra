"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  getDistributorContacts,
  createDistributorContact,
  updateDistributorContact,
  deleteDistributorContact,
  type CreateContactData,
} from "@/lib/api/distributor-portal";
import type { DistributorContact } from "@/types";

const KEY = "distributor-contacts";

export function useDistributorContacts() {
  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery<DistributorContact[], Error>({
    queryKey: [KEY],
    queryFn: getDistributorContacts,
  });

  const createMutation = useMutation({
    mutationFn: createDistributorContact,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: [KEY] }),
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: Partial<DistributorContact> }) => updateDistributorContact(id, data),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: [KEY] }),
  });

  const deleteMutation = useMutation({
    mutationFn: deleteDistributorContact,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: [KEY] }),
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

export type { CreateContactData };
export { getDistributorContacts, createDistributorContact, updateDistributorContact, deleteDistributorContact };
