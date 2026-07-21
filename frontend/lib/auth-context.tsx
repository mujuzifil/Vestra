"use client";

import { createContext, useContext, useState, useCallback, useEffect, type ReactNode } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { login as apiLogin, register as apiRegister, logout as apiLogout, getProfile } from "@/lib/api/auth";
import type { Customer, AuthResponse } from "@/types";

interface AuthContextValue {
  user: Customer | null;
  isLoading: boolean;
  isAuthenticated: boolean;
  login: (email: string, password: string) => Promise<AuthResponse>;
  register: (name: string, email: string, password: string, phone?: string) => Promise<AuthResponse>;
  logout: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const queryClient = useQueryClient();
  const [token, setToken] = useState<string | null>(() => {
    if (typeof window !== "undefined") {
      return localStorage.getItem("vestra_auth_token");
    }
    return null;
  });

  const { data: user, isLoading } = useQuery({
    queryKey: ["auth", "profile"],
    queryFn: getProfile,
    enabled: !!token,
    retry: false,
    staleTime: 5 * 60 * 1000,
  });

  const loginMutation = useMutation({
    mutationFn: ({ email, password }: { email: string; password: string }) => apiLogin(email, password),
  });

  const registerMutation = useMutation({
    mutationFn: ({ name, email, password, phone }: { name: string; email: string; password: string; phone?: string }) =>
      apiRegister(name, email, password, phone),
  });

  const logoutMutation = useMutation({
    mutationFn: apiLogout,
  });

  const handleAuthResult = useCallback(
    (result: AuthResponse) => {
      localStorage.setItem("vestra_auth_token", result.token);
      setToken(result.token);
      queryClient.setQueryData(["auth", "profile"], result.user);
      queryClient.invalidateQueries({ queryKey: ["cart"] });
    },
    [queryClient]
  );

  const login = useCallback(
    async (email: string, password: string) => {
      const result = await loginMutation.mutateAsync({ email, password });
      handleAuthResult(result);
      return result;
    },
    [loginMutation, handleAuthResult]
  );

  const register = useCallback(
    async (name: string, email: string, password: string, phone?: string) => {
      const result = await registerMutation.mutateAsync({ name, email, password, phone });
      handleAuthResult(result);
      return result;
    },
    [registerMutation, handleAuthResult]
  );

  const logout = useCallback(async () => {
    try {
      await logoutMutation.mutateAsync();
    } catch {
      // Ignore errors on logout
    }
    localStorage.removeItem("vestra_auth_token");
    localStorage.removeItem("vestra_cart");
    setToken(null);
    queryClient.clear();
  }, [logoutMutation, queryClient]);

  // Sync auth state across tabs
  useEffect(() => {
    const handleStorage = (e: StorageEvent) => {
      if (e.key === "vestra_auth_token") {
        setToken(e.newValue);
        if (!e.newValue) {
          queryClient.clear();
        }
      }
    };
    window.addEventListener("storage", handleStorage);
    return () => window.removeEventListener("storage", handleStorage);
  }, [queryClient]);

  const value: AuthContextValue = {
    user: user ?? null,
    isLoading: isLoading && !!token,
    isAuthenticated: !!user && !!token,
    login,
    register,
    logout,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error("useAuth must be used within AuthProvider");
  }
  return context;
}


