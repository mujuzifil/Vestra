"use client";

import { AuthProvider } from "@/lib/auth-context";
import { WishlistProvider } from "@/lib/wishlist-context";
import { Navbar } from "@/components/navigation/navbar";
import { Footer } from "@/components/layout/footer";
import { WhatsAppFloat } from "@/components/common/whatsapp-float";
import { Toaster } from "sonner";

export function RootLayoutClient({ children }: { children: React.ReactNode }) {
  return (
    <AuthProvider>
      <WishlistProvider>
        <div className="relative flex min-h-screen flex-col">
          <Navbar />
          <main className="flex-1">{children}</main>
          <Footer />
          <WhatsAppFloat />
          <Toaster
            position="top-right"
            richColors
            closeButton
            toastOptions={{
              style: {
                fontFamily: "var(--font-poppins), system-ui, sans-serif",
              },
            }}
          />
        </div>
      </WishlistProvider>
    </AuthProvider>
  );
}
