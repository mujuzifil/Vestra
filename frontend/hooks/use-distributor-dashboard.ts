"use client";

import { useQuery } from "@tanstack/react-query";
import { getDistributorDashboard } from "@/lib/api/distributor-portal";
import type { DistributorDashboard } from "@/types";

const KEY = "distributor-dashboard";

export function useDistributorDashboard() {
  const { data, isLoading, error } = useQuery<DistributorDashboard, Error>({
    queryKey: [KEY],
    queryFn: getDistributorDashboard,
  });

  return { data, isLoading, error };
}
