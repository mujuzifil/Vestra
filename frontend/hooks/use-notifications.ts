"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  getNotifications,
  getUnreadNotifications,
  markNotificationAsRead,
  markAllNotificationsAsRead,
  deleteNotification,
  getNotificationPreferences,
  updateNotificationPreferences,
} from "@/lib/api/notifications";
import type { CustomerPreferences, UpdateNotificationPreferencesData } from "@/types";
import type { Notification } from "@/lib/api/notifications";

const NOTIFICATIONS_KEY = ["notifications"];
const UNREAD_NOTIFICATIONS_KEY = ["notifications", "unread"];
const NOTIFICATION_PREFERENCES_KEY = ["notifications", "preferences"];

export function useNotifications(page: number = 1) {
  return useQuery<{ notifications: Notification[]; hasMore: boolean }, Error>({
    queryKey: [...NOTIFICATIONS_KEY, page],
    queryFn: async () => {
      const response = await getNotifications(page);
      return {
        notifications: response.data,
        hasMore: response.current_page < response.last_page,
      };
    },
  });
}

export function useUnreadNotifications() {
  return useQuery<{ notifications: Notification[]; total: number }, Error>({
    queryKey: UNREAD_NOTIFICATIONS_KEY,
    queryFn: async () => {
      const response = await getUnreadNotifications(1);
      return {
        notifications: response.data,
        total: response.total,
      };
    },
  });
}

export function useMarkNotificationAsRead() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: string) => markNotificationAsRead(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: NOTIFICATIONS_KEY });
      queryClient.invalidateQueries({ queryKey: UNREAD_NOTIFICATIONS_KEY });
    },
  });
}

export function useMarkAllNotificationsAsRead() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: markAllNotificationsAsRead,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: NOTIFICATIONS_KEY });
      queryClient.invalidateQueries({ queryKey: UNREAD_NOTIFICATIONS_KEY });
    },
  });
}

export function useDeleteNotification() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: string) => deleteNotification(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: NOTIFICATIONS_KEY });
      queryClient.invalidateQueries({ queryKey: UNREAD_NOTIFICATIONS_KEY });
    },
  });
}

export function useNotificationPreferences() {
  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery<CustomerPreferences, Error>({
    queryKey: NOTIFICATION_PREFERENCES_KEY,
    queryFn: getNotificationPreferences,
  });

  const updateMutation = useMutation({
    mutationFn: (payload: UpdateNotificationPreferencesData) => updateNotificationPreferences(payload),
    onSuccess: (updated) => {
      queryClient.setQueryData(NOTIFICATION_PREFERENCES_KEY, updated);
    },
  });

  return {
    data,
    isLoading,
    error,
    update: updateMutation.mutateAsync,
    isUpdating: updateMutation.isPending,
  };
}
