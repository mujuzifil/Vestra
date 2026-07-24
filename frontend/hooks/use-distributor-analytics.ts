"use client";

import { useQuery } from "@tanstack/react-query";
import { getDistributorAnalytics } from "@/lib/api/distributor-portal";
import type { DistributorAnalytics } from "@/types";

const KEY = "distributor-analytics";

export function useDistributorAnalytics() {
  const { data, isLoading, error } = useQuery<DistributorAnalytics, Error>({
    queryKey: [KEY],
    queryFn: getDistributorAnalytics,
  });

  return { data, isLoading, error };
}

export { getDistributorAnalytics };
