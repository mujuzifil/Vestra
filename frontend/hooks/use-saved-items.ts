import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import {
  getSavedItems,
  addToSavedItems,
  removeFromSavedItems,
} from "@/lib/api/saved-items";
import type { SavedItem, SavedItemsResponse } from "@/lib/api/saved-items";

const KEY = "saved-items";

export function useSavedItems(page = 1) {
  return useQuery<SavedItemsResponse, Error>({
    queryKey: [KEY, page],
    queryFn: () => getSavedItems(page),
    enabled: typeof window !== "undefined",
  });
}

export function useAddSavedItem() {
  const queryClient = useQueryClient();
  return useMutation<SavedItem, Error, number>({
    mutationFn: addToSavedItems,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [KEY] });
    },
  });
}

export function useRemoveSavedItem() {
  const queryClient = useQueryClient();
  return useMutation<void, Error, number>({
    mutationFn: removeFromSavedItems,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [KEY] });
    },
  });
}
