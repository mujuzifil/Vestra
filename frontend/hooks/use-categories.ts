"use client";

import { useQuery } from "@tanstack/react-query";
import { getCategories } from "@/lib/api/categories";
import type { Category } from "@/types";

const CATEGORIES_KEY = "categories";

export function useCategories() {
  return useQuery<Category[], Error>({
    queryKey: [CATEGORIES_KEY],
    queryFn: getCategories,
  });
}
