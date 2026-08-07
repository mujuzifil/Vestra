"use client";

import { useEffect, useState } from "react";
import { usePathname, useRouter } from "next/navigation";
import { Loader2 } from "lucide-react";
import {
  DistributorMobileNavBar,
  DistributorSidebar,
  useDistributorActiveLabel,
} from "@/components/distributor/distributor-sidebar";
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
  const pathname = usePathname();
  const { user, isAuthenticated, isLoading } = useAuth();
  const [mobileOpen, setMobileOpen] = useState(false);
  const activeLabel = useDistributorActiveLabel();

  useEffect(() => {
    if (!isLoading && !isAuthenticated) {
      router.push("/auth/login");
      return;
    }

    if (!isLoading && isAuthenticated && user && !user.roles?.includes("distributor")) {
      router.push("/distributor");
    }
  }, [isLoading, isAuthenticated, user, router]);

  useEffect(() => {
    setMobileOpen(false);
  }, [pathname]);

  useEffect(() => {
    if (!mobileOpen) return;
    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => {
      document.body.style.overflow = previousOverflow;
    };
  }, [mobileOpen]);

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
      <DistributorMobileNavBar
        activeLabel={activeLabel}
        onOpen={() => setMobileOpen(true)}
        mobileOpen={mobileOpen}
      />

      <div className="flex w-full min-w-0">
        <DistributorSidebar mobileOpen={mobileOpen} setMobileOpen={setMobileOpen} />
        <main className="flex-1 min-w-0 max-w-full pt-[56px] lg:pt-0">
          <div className="p-4 sm:p-6 lg:p-8 min-w-0 max-w-full">{children}</div>
        </main>
      </div>
    </div>
  );
}
