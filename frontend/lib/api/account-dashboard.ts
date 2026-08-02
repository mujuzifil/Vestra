import { apiGet } from "./client";
import type { ApiResponse, AccountDashboard } from "@/types";

export async function getAccountDashboard(): Promise<AccountDashboard> {
  const response = await apiGet<ApiResponse<AccountDashboard>>("/account/dashboard");
  return response.data;
}
