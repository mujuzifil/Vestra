"use client";

import { useQuery } from "@tanstack/react-query";
import { getActivity, type ActivityResponse } from "@/lib/api/auth";

const ACTIVITY_KEY = ["auth", "activity"];

export function useActivity(page: number = 1) {
  return useQuery<ActivityResponse, Error>({
    queryKey: [...ACTIVITY_KEY, page],
    queryFn: () => getActivity(page),
  });
}
