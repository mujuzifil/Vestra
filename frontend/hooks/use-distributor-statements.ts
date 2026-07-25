"use client";

import { useQuery } from "@tanstack/react-query";
import { getDistributorStatement } from "@/lib/api/distributor-portal";
import type { DistributorStatement } from "@/types";

const KEY = "distributor-statement";

export function useDistributorStatement(params?: { start?: string; end?: string }) {
  const { data, isLoading, error } = useQuery<DistributorStatement, Error>({
    queryKey: [KEY, params],
    queryFn: () => getDistributorStatement(params),
  });

  return { data, isLoading, error };
}

export { getDistributorStatement };
