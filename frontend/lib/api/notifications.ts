import { apiGet, apiPut, apiDelete } from "./client";
import type { ApiResponse, PaginatedResponse, CustomerPreferences, NotificationPreferences } from "@/types";

export interface Notification {
  id: string;
  type: string;
  title: string | null;
  message: string | null;
  template_key: string | null;
  priority: string;
  action_url: string | null;
  data: Record<string, unknown>;
  read_at: string | null;
  created_at: string;
}

export interface Announcement {
  id: number;
  title: string;
  body: string;
  target_audience: string;
  priority: string;
  pinned: boolean;
  start_at: string | null;
  end_at: string | null;
  sent_at: string | null;
  created_at: string;
}

export async function getNotifications(page: number = 1): Promise<PaginatedResponse<Notification>> {
  const response = await apiGet<ApiResponse<PaginatedResponse<Notification>>>(`/notifications?page=${page}`);
  return response.data;
}

export async function getUnreadNotifications(page: number = 1): Promise<PaginatedResponse<Notification>> {
  const response = await apiGet<ApiResponse<PaginatedResponse<Notification>>>(`/notifications/unread?page=${page}`);
  return response.data;
}

export async function markNotificationAsRead(id: string): Promise<void> {
  await apiPut<ApiResponse<null>>(`/notifications/${id}`, {});
}

export async function markAllNotificationsAsRead(): Promise<void> {
  await apiPut<ApiResponse<null>>("/notifications/read-all", {});
}

export async function deleteNotification(id: string): Promise<void> {
  await apiDelete<ApiResponse<null>>(`/notifications/${id}`);
}

export async function getNotificationPreferences(): Promise<CustomerPreferences> {
  const response = await apiGet<ApiResponse<CustomerPreferences>>("/notifications/preferences");
  return response.data;
}

export async function updateNotificationPreferences(data: { notification_preferences?: NotificationPreferences; system_alerts?: boolean; emergency_alerts?: boolean }): Promise<CustomerPreferences> {
  const response = await apiPut<ApiResponse<CustomerPreferences>>("/notifications/preferences", data);
  return response.data;
}

export async function getAnnouncements(): Promise<Announcement[]> {
  const response = await apiGet<ApiResponse<Announcement[]>>("/announcements");
  return response.data;
}
