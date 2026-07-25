"use client";

import { useQuery } from "@tanstack/react-query";
import { getDistributorNotifications } from "@/lib/api/distributor-portal";
import type { DistributorNotification } from "@/types";

const KEY = "distributor-notifications";

export function useDistributorNotifications() {
  const { data, isLoading, error } = useQuery<DistributorNotification[], Error>({
    queryKey: [KEY],
    queryFn: getDistributorNotifications,
  });

  return { data, isLoading, error };
}

export { getDistributorNotifications };
