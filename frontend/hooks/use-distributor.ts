"use client";

import { useMutation } from "@tanstack/react-query";
import { submitDistributor } from "@/lib/api/distributor";
import type { DistributorFormData } from "@/types";

export function useDistributorMutation() {
  return useMutation({
    mutationFn: (data: DistributorFormData) => submitDistributor(data),
  });
}
