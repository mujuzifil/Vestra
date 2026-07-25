"use client";

import { useQuery } from "@tanstack/react-query";
import { getDistributorProducts, getDistributorProduct } from "@/lib/api/distributor-portal";
import type { DistributorProduct } from "@/types";

const KEY = "distributor-products";

export function useDistributorProducts() {
  const { data, isLoading, error } = useQuery<DistributorProduct[], Error>({
    queryKey: [KEY],
    queryFn: getDistributorProducts,
  });

  return { data, isLoading, error };
}

export function useDistributorProduct(slug: string) {
  const { data, isLoading, error } = useQuery<DistributorProduct, Error>({
    queryKey: [KEY, slug],
    queryFn: () => getDistributorProduct(slug),
    enabled: !!slug,
  });

  return { data, isLoading, error };
}

export { getDistributorProducts, getDistributorProduct };
