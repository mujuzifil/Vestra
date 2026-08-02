"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { getSupportTicket, replyToSupportTicket } from "@/lib/api/account-support";
import type { SupportTicket } from "@/types";

const KEY = ["account", "support", "ticket"];

export function useSupportTicket(id: number) {
  return useQuery<SupportTicket, Error>({
    queryKey: [...KEY, id],
    queryFn: () => getSupportTicket(id),
    enabled: typeof window !== "undefined" && id > 0,
  });
}

export function useReplyToSupportTicket(id: number) {
  const queryClient = useQueryClient();

  return useMutation<SupportTicket, Error, { message: string; attachments?: FileList | null }>({
    mutationFn: (data) => replyToSupportTicket(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: KEY });
      queryClient.invalidateQueries({ queryKey: ["account", "support"] });
    },
  });
}
