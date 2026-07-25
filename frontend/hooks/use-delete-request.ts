"use client";

import { useMutation } from "@tanstack/react-query";
import { requestAccountDeletion } from "@/lib/api/auth";

export function useDeleteRequest() {
  const mutation = useMutation({
    mutationFn: ({ reason, password }: { reason?: string; password?: string }) =>
      requestAccountDeletion(reason, password),
  });

  return {
    submit: mutation.mutateAsync,
    isSubmitting: mutation.isPending,
  };
}
