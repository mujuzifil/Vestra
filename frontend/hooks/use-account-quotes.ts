"use client";

import { useQuery } from "@tanstack/react-query";
import { getAccountQuotes } from "@/lib/api/account-quotes";
import type { PaginatedResponse, CustomerQuote } from "@/types";

const KEY = ["account", "quotes"];

export function useAccountQuotes(page = 1) {
  return useQuery<PaginatedResponse<CustomerQuote>, Error>({
    queryKey: [...KEY, page],
    queryFn: () => getAccountQuotes(page),
    enabled: typeof window !== "undefined",
  });
}
