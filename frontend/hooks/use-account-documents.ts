"use client";

import { useQuery } from "@tanstack/react-query";
import { getAccountDocuments } from "@/lib/api/account-documents";
import type { PaginatedResponse, CustomerDocument } from "@/types";

const KEY = ["account", "documents"];

export function useAccountDocuments(page = 1) {
  return useQuery<PaginatedResponse<CustomerDocument>, Error>({
    queryKey: [...KEY, page],
    queryFn: () => getAccountDocuments(page),
    enabled: typeof window !== "undefined",
  });
}
