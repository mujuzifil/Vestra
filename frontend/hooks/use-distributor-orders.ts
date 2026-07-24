"use client";

import { useQuery } from "@tanstack/react-query";
import { getDistributorOrders, getDistributorOrder } from "@/lib/api/distributor-portal";
import type { DistributorOrder } from "@/types";

const KEY = "distributor-orders";

export function useDistributorOrders() {
  const { data, isLoading, error } = useQuery<DistributorOrder[], Error>({
    queryKey: [KEY],
    queryFn: getDistributorOrders,
  });

  return { data, isLoading, error };
}

export function useDistributorOrder(id: number) {
  const { data, isLoading, error } = useQuery<DistributorOrder, Error>({
    queryKey: [KEY, id],
    queryFn: () => getDistributorOrder(id),
    enabled: !!id,
  });

  return { data, isLoading, error };
}

export { getDistributorOrders, getDistributorOrder };
