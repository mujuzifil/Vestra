"use client";

import { AuthProvider } from "@/lib/auth-context";
import { CartProvider } from "@/lib/cart-context";
import { Navbar } from "@/components/navigation/navbar";
import { Footer } from "@/components/layout/footer";
import { WhatsAppFloat } from "@/components/common/whatsapp-float";
import { Toaster } from "sonner";

export function RootLayoutClient({ children }: { children: React.ReactNode }) {
  return (
    <AuthProvider>
      <CartProvider>
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
      </CartProvider>
    </AuthProvider>
  );
}
