"use client";

import { usePathname } from "next/navigation";
import { DistributorLayout } from "@/components/layout/distributor-layout";

/** Public marketing routes under /distributor — must not use the portal shell. */
const PUBLIC_DISTRIBUTOR_PREFIXES = ["/distributor/success"];

function isPublicDistributorRoute(pathname: string | null): boolean {
  if (!pathname) return false;
  if (pathname === "/distributor") return true;
  return PUBLIC_DISTRIBUTOR_PREFIXES.some(
    (prefix) => pathname === prefix || pathname.startsWith(`${prefix}/`)
  );
}

export default function DistributorRouteLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  const pathname = usePathname();

  if (isPublicDistributorRoute(pathname)) {
    return <>{children}</>;
  }

  return <DistributorLayout>{children}</DistributorLayout>;
}
