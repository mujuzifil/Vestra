"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { getSupportTickets, createSupportTicket } from "@/lib/api/account-support";
import type { PaginatedResponse, SupportTicket } from "@/types";
import type { CreateSupportTicketData } from "@/lib/api/account-support";

const KEY = ["account", "support"];

export function useSupportTickets(page = 1) {
  return useQuery<PaginatedResponse<SupportTicket>, Error>({
    queryKey: [...KEY, page],
    queryFn: () => getSupportTickets(page),
    enabled: typeof window !== "undefined",
  });
}

export function useCreateSupportTicket() {
  const queryClient = useQueryClient();

  return useMutation<SupportTicket, Error, CreateSupportTicketData>({
    mutationFn: createSupportTicket,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: KEY });
    },
  });
}
