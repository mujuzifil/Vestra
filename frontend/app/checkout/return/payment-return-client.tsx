"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { CheckCircle, XCircle, Loader2, Package, ArrowRight } from "lucide-react";
import { Container } from "@/components/common/container";

interface Props {
  status: string;
  txRef: string;
  transactionId: string;
}

export function PaymentReturnClient({ status, txRef, transactionId }: Props) {
  const [verifying, setVerifying] = useState(true);
  const [verified, setVerified] = useState(false);
  const [error, setError] = useState("");

  useEffect(() => {
    if (!txRef) {
      setVerifying(false);
      setError("Invalid payment reference.");
      return;
    }

    async function verifyPayment() {
      try {
        const res = await fetch(`/api/payments/verify?reference=${encodeURIComponent(txRef)}`, {
          headers: { Accept: "application/json" },
        });
        const data = await res.json();
        if (data.success) {
          setVerified(true);
        } else {
          setError(data.message || "Payment verification failed.");
        }
      } catch {
        setError("Could not verify payment. Please contact support.");
      } finally {
        setVerifying(false);
      }
    }

    verifyPayment();
  }, [txRef]);

  if (verifying) {
    return (
      <div className="min-h-[calc(100vh-88px)] flex items-center justify-center bg-[#f8fafc] py-12">
        <Container className="max-w-md w-full text-center">
          <div className="bg-white rounded-[24px] border border-[#e2e8f0] shadow-lg p-8 lg:p-10">
            <Loader2 className="w-16 h-16 text-green-500 mx-auto mb-6 animate-spin" />
            <h1 className="text-2xl font-extrabold text-[#0a1628] mb-2">Verifying Payment...</h1>
            <p className="text-[#64748b]">Please wait while we confirm your payment.</p>
          </div>
        </Container>
      </div>
    );
  }

  if (verified) {
    return (
      <div className="min-h-[calc(100vh-88px)] flex items-center justify-center bg-[#f8fafc] py-12">
        <Container className="max-w-md w-full text-center">
          <div className="bg-white rounded-[24px] border border-[#e2e8f0] shadow-lg p-8 lg:p-10">
            <CheckCircle className="w-16 h-16 text-green-500 mx-auto mb-6" />
            <h1 className="text-2xl lg:text-3xl font-extrabold text-[#0a1628] mb-2">Payment Successful!</h1>
            <p className="text-[#64748b] mb-6">
              Your payment has been confirmed and your order is being processed.
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

  return (
    <div className="min-h-[calc(100vh-88px)] flex items-center justify-center bg-[#f8fafc] py-12">
      <Container className="max-w-md w-full text-center">
        <div className="bg-white rounded-[24px] border border-[#e2e8f0] shadow-lg p-8 lg:p-10">
          <XCircle className="w-16 h-16 text-red-500 mx-auto mb-6" />
          <h1 className="text-2xl lg:text-3xl font-extrabold text-[#0a1628] mb-2">Payment Issue</h1>
          <p className="text-[#64748b] mb-2">{error || "We could not verify your payment."}</p>
          <p className="text-sm text-[#94a3b8] mb-6">
            Reference: {txRef || "N/A"}
            {transactionId && <br />}
            {transactionId && `Transaction ID: ${transactionId}`}
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
              href="/contact"
              className="w-full inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-full font-semibold text-[#0a1628] bg-white border border-[#e2e8f0] hover:bg-[#f8fafc] transition-colors"
            >
              Contact Support
            </Link>
          </div>
        </div>
      </Container>
    </div>
  );
}
