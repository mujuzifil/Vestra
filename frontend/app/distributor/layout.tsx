"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import { Loader2 } from "lucide-react";
import { DistributorSidebar } from "@/components/distributor/distributor-sidebar";
import { useAuth } from "@/lib/auth-context";

export default function DistributorLayout({ children }: { children: React.ReactNode }) {
  const router = useRouter();
  const { user, isAuthenticated, isLoading } = useAuth();

  useEffect(() => {
    if (!isLoading && !isAuthenticated) {
      router.push("/auth/login");
      return;
    }

    if (!isLoading && isAuthenticated && user && !user.roles?.includes("distributor")) {
      router.push("/account");
    }
  }, [isLoading, isAuthenticated, user, router]);

  if (isLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-green-500" />
      </div>
    );
  }

  if (!isAuthenticated || !user?.roles?.includes("distributor")) {
    return null;
  }

  return (
    <div className="min-h-screen bg-[#f8fafc]">
      <div className="flex">
        <DistributorSidebar />
        <main className="flex-1 min-w-0 lg:ml-0 pt-16 lg:pt-0">
          <div className="p-4 sm:p-6 lg:p-8">{children}</div>
        </main>
      </div>
    </div>
  );
}
