"use client";

import { useMutation } from "@tanstack/react-query";
import { login, register, logout, changePassword } from "@/lib/api/auth";
import { useAuth } from "@/lib/auth-context";

export function useLogin() {
  const auth = useAuth();
  return useMutation({
    mutationFn: ({ email, password }: { email: string; password: string }) =>
      auth.login(email, password),
  });
}

export function useRegister() {
  const auth = useAuth();
  return useMutation({
    mutationFn: ({ name, email, password, phone }: { name: string; email: string; password: string; phone?: string }) =>
      auth.register(name, email, password, phone),
  });
}

export function useLogout() {
  const auth = useAuth();
  return useMutation({
    mutationFn: () => auth.logout(),
  });
}

export function useChangePassword() {
  return useMutation({
    mutationFn: ({ currentPassword, password }: { currentPassword: string; password: string }) =>
      changePassword(currentPassword, password),
  });
}
