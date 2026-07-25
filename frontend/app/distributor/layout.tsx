import { DistributorLayout } from "@/components/layout/distributor-layout";

export default function DistributorRouteLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return <DistributorLayout>{children}</DistributorLayout>;
}
