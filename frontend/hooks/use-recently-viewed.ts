import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import {
  getRecentlyViewed,
  recordProductView,
  removeRecentlyViewed,
  clearRecentlyViewed,
} from "@/lib/api/recently-viewed";

const KEY = "recently-viewed";

export function useRecentlyViewed(page = 1) {
  return useQuery({
    queryKey: [KEY, page],
    queryFn: () => getRecentlyViewed(page),
    enabled: typeof window !== "undefined",
  });
}

export function useRecordProductView() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: recordProductView,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [KEY] });
    },
  });
}

export function useRemoveRecentlyViewed() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: removeRecentlyViewed,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [KEY] });
    },
  });
}

export function useClearRecentlyViewed() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: clearRecentlyViewed,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [KEY] });
    },
  });
}
