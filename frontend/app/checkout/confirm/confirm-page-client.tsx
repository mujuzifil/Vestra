"use client";

import Link from "next/link";
import { CheckCircle, Package, ArrowRight } from "lucide-react";
import { Container } from "@/components/common/container";

export function ConfirmPageClient() {
  return (
    <div className="min-h-[calc(100vh-88px)] flex items-center justify-center bg-[#f8fafc] py-12">
      <Container className="max-w-md w-full text-center">
        <div className="bg-white rounded-[24px] border border-[#e2e8f0] shadow-lg p-8 lg:p-10">
          <CheckCircle className="w-16 h-16 text-green-500 mx-auto mb-6" />
          <h1 className="text-2xl lg:text-3xl font-extrabold text-[#0a1628] mb-2">Order Placed!</h1>
          <p className="text-[#64748b] mb-6">
            Thank you for your order. We will process it and get back to you soon.
          </p>

          <div className="space-y-3">
            <Link
              href="/account/orders"
              className="w-full inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-1 transition-all"
            >
              <Package className="w-4 h-4" />
              View My Orders
            </Link>
            <Link
              href="/products"
              className="w-full inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-full font-semibold text-[#0a1628] bg-white border border-[#e2e8f0] hover:bg-[#f8fafc] transition-colors"
            >
              Continue Shopping
              <ArrowRight className="w-4 h-4" />
            </Link>
          </div>
        </div>
      </Container>
    </div>
  );
}
