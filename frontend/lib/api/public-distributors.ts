import { apiGet } from "./client";
import type {
  ApiResponse,
  PublicDistributor,
  DistributorDirectoryFilters,
  DistributorNetworkStats,
  CoverageRegions,
} from "@/types";

function buildQueryString(params: DistributorDirectoryFilters): string {
  const qs = new URLSearchParams();
  Object.entries(params).forEach(([key, value]) => {
    if (value === undefined || value === null || value === "") return;
    qs.append(key, String(value));
  });
  const query = qs.toString();
  return query ? `?${query}` : "";
}

export async function getPublicDistributors(
  filters: DistributorDirectoryFilters = {}
): Promise<PublicDistributor[]> {
  const response = await apiGet<ApiResponse<PublicDistributor[]>>(
    `/public/distributors${buildQueryString(filters)}`
  );
  return response.data;
}

export async function getDistributorNetworkStats(): Promise<DistributorNetworkStats> {
  const response = await apiGet<ApiResponse<DistributorNetworkStats>>("/public/distributors/stats");
  return response.data;
}

export async function getCoverageRegions(): Promise<CoverageRegions> {
  const response = await apiGet<ApiResponse<CoverageRegions>>("/public/distributors/coverage");
  return response.data;
}
