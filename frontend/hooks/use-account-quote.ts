"use client";

import { useQuery } from "@tanstack/react-query";
import { getAccountQuote } from "@/lib/api/account-quotes";
import type { CustomerQuote } from "@/types";

const KEY = ["account", "quote"];

export function useAccountQuote(id: number) {
  return useQuery<CustomerQuote, Error>({
    queryKey: [...KEY, id],
    queryFn: () => getAccountQuote(id),
    enabled: typeof window !== "undefined" && id > 0,
  });
}
