"use client";

import { useQuery } from "@tanstack/react-query";
import { getDistributorApplicationStatus } from "@/lib/api/distributor-portal";
import type { DistributorRequest } from "@/types";

const KEY = "distributor-application-status";

export function useDistributorApplicationStatus(enabled = true) {
  const { data, isLoading, error } = useQuery<DistributorRequest | null, Error>({
    queryKey: [KEY],
    queryFn: getDistributorApplicationStatus,
    enabled,
  });

  return { data, isLoading, error };
}

export { getDistributorApplicationStatus };
