"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  getDistributorDocuments,
  uploadDistributorDocument,
  deleteDistributorDocument,
  type CreateDocumentData,
} from "@/lib/api/distributor-portal";
import type { DistributorDocument } from "@/types";

const KEY = "distributor-documents";

export function useDistributorDocuments() {
  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery<DistributorDocument[], Error>({
    queryKey: [KEY],
    queryFn: getDistributorDocuments,
  });

  const uploadMutation = useMutation({
    mutationFn: uploadDistributorDocument,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: [KEY] }),
  });

  const deleteMutation = useMutation({
    mutationFn: deleteDistributorDocument,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: [KEY] }),
  });

  return {
    data,
    isLoading,
    error,
    upload: uploadMutation.mutateAsync,
    remove: deleteMutation.mutateAsync,
  };
}

export type { CreateDocumentData };
export { getDistributorDocuments, uploadDistributorDocument, deleteDistributorDocument };
