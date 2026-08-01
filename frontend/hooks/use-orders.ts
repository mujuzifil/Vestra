"use client";

import { useQuery } from "@tanstack/react-query";
import { getOrders, getOrder } from "@/lib/api/orders";
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
