"use client";

import { useQuery } from "@tanstack/react-query";
import { getAccountDashboard } from "@/lib/api/account-dashboard";
import type { AccountDashboard } from "@/types";

const KEY = ["account", "dashboard"];

export function useAccountDashboard(enabled = true) {
  return useQuery<AccountDashboard, Error>({
    queryKey: KEY,
    queryFn: getAccountDashboard,
    enabled,
  });
}
