"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  getDistributorBranches,
  createDistributorBranch,
  updateDistributorBranch,
  deleteDistributorBranch,
  type CreateBranchData,
} from "@/lib/api/distributor-portal";
import type { DistributorBranch } from "@/types";

const KEY = "distributor-branches";

export function useDistributorBranches() {
  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery<DistributorBranch[], Error>({
    queryKey: [KEY],
    queryFn: getDistributorBranches,
  });

  const createMutation = useMutation({
    mutationFn: createDistributorBranch,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: [KEY] }),
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: Partial<DistributorBranch> }) => updateDistributorBranch(id, data),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: [KEY] }),
  });

  const deleteMutation = useMutation({
    mutationFn: deleteDistributorBranch,
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

export type { CreateBranchData };
export { getDistributorBranches, createDistributorBranch, updateDistributorBranch, deleteDistributorBranch };
