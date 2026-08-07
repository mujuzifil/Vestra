"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import { Loader2 } from "lucide-react";
import { DistributorSidebar } from "@/components/distributor/distributor-sidebar";
import { useAuth } from "@/lib/auth-context";
import { cn } from "@/lib/utils";

export function DistributorLayout({
  children,
  className,
}: {
  children: React.ReactNode;
  className?: string;
}) {
  const router = useRouter();
  const { user, isAuthenticated, isLoading } = useAuth();

  useEffect(() => {
    if (!isLoading && !isAuthenticated) {
      router.push("/auth/login");
      return;
    }

    if (!isLoading && isAuthenticated && user && !user.roles?.includes("distributor")) {
      router.push("/distributor");
    }
  }, [isLoading, isAuthenticated, user, router]);

  if (isLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
      </div>
    );
  }

  if (!isAuthenticated || !user?.roles?.includes("distributor")) {
    return null;
  }

  return (
    <div className={cn("min-h-screen w-full max-w-full min-w-0 overflow-x-clip bg-surface-page", className)}>
      <div className="flex w-full min-w-0">
        <DistributorSidebar />
        <main className="flex-1 min-w-0 max-w-full lg:ml-0 pt-16 lg:pt-0">
          <div className="p-4 sm:p-6 lg:p-8 min-w-0 max-w-full">{children}</div>
        </main>
      </div>
    </div>
  );
}
