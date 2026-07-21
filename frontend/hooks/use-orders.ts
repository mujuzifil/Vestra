"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { getOrders, getOrder, checkout } from "@/lib/api/orders";
import type { Order } from "@/types";

const ORDERS_KEY = "orders";

export function useOrders() {
  return useQuery<Order[], Error>({
    queryKey: [ORDERS_KEY],
    queryFn: getOrders,
  });
}

export function useOrder(id: number) {
  return useQuery<Order, Error>({
    queryKey: [ORDERS_KEY, id],
    queryFn: () => getOrder(id),
    enabled: !!id,
  });
}

export function useCheckout() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: checkout,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [ORDERS_KEY] });
      queryClient.invalidateQueries({ queryKey: ["cart"] });
    },
  });
}
