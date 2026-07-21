import { Metadata } from "next";
import { PaymentReturnClient } from "./payment-return-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Payment Confirmation",
  description: "Verify your payment status.",
  pathname: "/checkout/return",
});

interface Props {
  searchParams: Promise<{ [key: string]: string | string[] | undefined }>;
}

export default async function PaymentReturnPage({ searchParams }: Props) {
  const params = await searchParams;
  const status = typeof params.status === "string" ? params.status : "";
  const txRef = typeof params.tx_ref === "string" ? params.tx_ref : "";
  const transactionId = typeof params.transaction_id === "string" ? params.transaction_id : "";

  return (
    <PaymentReturnClient
      status={status}
      txRef={txRef}
      transactionId={transactionId}
    />
  );
}
