"use client";

import { useQuery } from "@tanstack/react-query";
import { getDistributorInvoices, getDistributorInvoice } from "@/lib/api/distributor-portal";
import type { DistributorInvoice } from "@/types";

const KEY = "distributor-invoices";

export function useDistributorInvoices() {
  const { data, isLoading, error } = useQuery<DistributorInvoice[], Error>({
    queryKey: [KEY],
    queryFn: getDistributorInvoices,
  });

  return { data, isLoading, error };
}

export function useDistributorInvoice(id: number) {
  const { data, isLoading, error } = useQuery<DistributorInvoice, Error>({
    queryKey: [KEY, id],
    queryFn: () => getDistributorInvoice(id),
    enabled: !!id,
  });

  return { data, isLoading, error };
}

export { getDistributorInvoices, getDistributorInvoice };
