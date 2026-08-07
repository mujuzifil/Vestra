import { Poppins } from "next/font/google";
import "./globals.css";
import { QueryProvider } from "@/lib/query-provider";
import { RootLayoutClient } from "@/components/layout/root-layout-client";
import { SkipLink } from "@/components/common/skip-link";
import { createMetadata } from "@/lib/metadata";

const poppins = Poppins({
  variable: "--font-poppins",
  subsets: ["latin"],
  weight: ["300", "400", "500", "600", "700", "800", "900"],
  display: "swap",
});

export const metadata = createMetadata({
  title: "VESTRA",
  description:
    "VESTRA is a premium fabric care brand dedicated to developing high-performance cleaning solutions that combine advanced chemistry, innovation, and exceptional garment care.",
  pathname: "/",
});

export const viewport = {
  width: "device-width",
  initialScale: 1,
  viewportFit: "cover" as const,
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en" className="scroll-smooth overflow-x-clip">
      <body className={`${poppins.variable} font-sans antialiased overflow-x-clip max-w-full`}>
        <SkipLink />
        <QueryProvider>
          <RootLayoutClient>{children}</RootLayoutClient>
        </QueryProvider>
      </body>
    </html>
  );
}
