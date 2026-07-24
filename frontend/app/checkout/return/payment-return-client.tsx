"use client";

import { useEffect, useRef, useState } from "react";
import Link from "next/link";
import { CheckCircle, XCircle, Loader2, Package, ArrowRight, AlertCircle, RefreshCcw, ShoppingBag } from "lucide-react";
import { Container } from "@/components/common/container";
import { verifyPayment, getTransaction, initiatePayment } from "@/lib/api/payments";
import { toastError } from "@/lib/toast-utils";

interface Props {
  status: string;
  txRef: string;
  transactionId: string;
}

type ReturnStatus = "verifying" | "success" | "cancelled" | "failed" | "pending" | "error";

export function PaymentReturnClient({ status, txRef, transactionId }: Props) {
  const [returnStatus, setReturnStatus] = useState<ReturnStatus>("verifying");
  const [message, setMessage] = useState("Please wait while we confirm your payment.");
  const [orderId, setOrderId] = useState<number | null>(null);
  const [isRetrying, setIsRetrying] = useState(false);
  const verifiedRef = useRef(false);

  useEffect(() => {
    if (!txRef) {
      setReturnStatus("error");
      setMessage("Invalid payment reference.");
      return;
    }

    if (verifiedRef.current) return;
    verifiedRef.current = true;

    async function verify() {
      try {
        const verifyResult = await verifyPayment(txRef);

        if (verifyResult.success) {
          setReturnStatus("success");
          setMessage(verifyResult.message || "Payment confirmed.");
          return;
        }

        // Verification failed — inspect transaction state for a clearer message.
        try {
          const transaction = await getTransaction(txRef);
          setOrderId(transaction.order_id ?? null);

          if (transaction.status === "pending") {
            setReturnStatus("pending");
            setMessage(verifyResult.message || "Payment is still being processed.");
          } else if (transaction.status === "failed") {
            setReturnStatus("failed");
            setMessage(verifyResult.message || "Payment could not be completed.");
          } else {
            setReturnStatus(status === "cancelled" ? "cancelled" : "failed");
            setMessage(verifyResult.message || "Payment was not successful.");
          }
        } catch {
          setReturnStatus(status === "cancelled" ? "cancelled" : "failed");
          setMessage(verifyResult.message || "Payment could not be verified.");
        }
      } catch (err) {
        setReturnStatus("error");
        const errorMessage = err instanceof Error ? err.message : "Could not verify payment.";
        setMessage(errorMessage);
      }
    }

    verify();
  }, [txRef, status]);

  async function handleRetry() {
    if (!orderId || !txRef) return;
    setIsRetrying(true);
    try {
      const result = await initiatePayment(orderId);
      window.location.href = result.payment_link;
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : "Could not restart payment.";
      toastError(errorMessage);
      setIsRetrying(false);
    }
  }

  if (returnStatus === "verifying") {
    return (
      <div className="min-h-[calc(100vh-88px)] flex items-center justify-center bg-[#f8fafc] py-12">
        <Container className="max-w-md w-full text-center">
          <div className="bg-white rounded-[24px] border border-[#e2e8f0] shadow-lg p-8 lg:p-10">
            <Loader2 className="w-16 h-16 text-green-500 mx-auto mb-6 animate-spin" />
            <h1 className="text-2xl font-extrabold text-[#0a1628] mb-2">Verifying Payment...</h1>
            <p className="text-[#64748b]">{message}</p>
          </div>
        </Container>
      </div>
    );
  }

  if (returnStatus === "success") {
    return (
      <div className="min-h-[calc(100vh-88px)] flex items-center justify-center bg-[#f8fafc] py-12">
        <Container className="max-w-md w-full text-center">
          <div className="bg-white rounded-[24px] border border-[#e2e8f0] shadow-lg p-8 lg:p-10">
            <CheckCircle className="w-16 h-16 text-green-500 mx-auto mb-6" />
            <h1 className="text-2xl lg:text-3xl font-extrabold text-[#0a1628] mb-2">Payment Successful!</h1>
            <p className="text-[#64748b] mb-6">
              {message || "Your payment has been confirmed and your order is being processed."}
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
                <ShoppingBag className="w-4 h-4" />
                Continue Shopping
              </Link>
            </div>
          </div>
        </Container>
      </div>
    );
  }

  const isFailure = returnStatus === "failed" || returnStatus === "error";
  const isCancelled = returnStatus === "cancelled";
  const isPending = returnStatus === "pending";

  return (
    <div className="min-h-[calc(100vh-88px)] flex items-center justify-center bg-[#f8fafc] py-12">
      <Container className="max-w-md w-full text-center">
        <div className="bg-white rounded-[24px] border border-[#e2e8f0] shadow-lg p-8 lg:p-10">
          {isPending ? (
            <AlertCircle className="w-16 h-16 text-amber-500 mx-auto mb-6" />
          ) : (
            <XCircle className="w-16 h-16 text-red-500 mx-auto mb-6" />
          )}
          <h1 className="text-2xl lg:text-3xl font-extrabold text-[#0a1628] mb-2">
            {isPending ? "Payment Pending" : isCancelled ? "Payment Cancelled" : "Payment Issue"}
          </h1>
          <p className="text-[#64748b] mb-2">{message}</p>
          <p className="text-sm text-[#94a3b8] mb-6">
            Reference: {txRef || "N/A"}
            {transactionId && <br />}
            {transactionId && `Transaction ID: ${transactionId}`}
          </p>

          <div className="space-y-3">
            {(isFailure || isCancelled || isPending) && orderId && (
              <button
                onClick={handleRetry}
                disabled={isRetrying}
                className="w-full inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-1 disabled:opacity-60 disabled:cursor-not-allowed transition-all"
              >
                {isRetrying ? (
                  <Loader2 className="w-4 h-4 animate-spin" />
                ) : (
                  <RefreshCcw className="w-4 h-4" />
                )}
                {isRetrying ? "Retrying..." : "Retry Payment"}
              </button>
            )}
            <Link
              href="/account/orders"
              className="w-full inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-full font-semibold text-[#0a1628] bg-white border border-[#e2e8f0] hover:bg-[#f8fafc] transition-colors"
            >
              <Package className="w-4 h-4" />
              View My Orders
            </Link>
            <Link
              href="/checkout"
              className="w-full inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-full font-semibold text-[#0a1628] bg-white border border-[#e2e8f0] hover:bg-[#f8fafc] transition-colors"
            >
              <ArrowRight className="w-4 h-4" />
              Return to Checkout
            </Link>
          </div>
        </div>
      </Container>
    </div>
  );
}
