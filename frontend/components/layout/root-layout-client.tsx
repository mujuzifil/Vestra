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
        <div className="relative flex min-h-screen w-full max-w-[100vw] min-w-0 flex-col overflow-x-clip">
          <Navbar />
          <main className="flex-1 w-full min-w-0 max-w-full overflow-x-clip">{children}</main>
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
