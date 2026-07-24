import { CustomerLayout } from "@/components/layout/customer-layout";

export default function AccountLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return <CustomerLayout>{children}</CustomerLayout>;
}
