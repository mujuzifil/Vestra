"use client";

import { useState } from "react";
import { Loader2, Upload, FileText } from "lucide-react";
import { InputField, TextareaField } from "@/components/common/form-field";
import { EmptyState } from "@/components/common/empty-state";
import { useDistributorPayments } from "@/hooks/use-distributor-payments";
import { toastSuccess, toastError } from "@/lib/toast-utils";

export function PaymentsPageClient() {
  const { data: payments, isLoading, create } = useDistributorPayments();
  const [amount, setAmount] = useState("");
  const [reference, setReference] = useState("");
  const [notes, setNotes] = useState("");
  const [file, setFile] = useState<File | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (!amount || !reference) {
      toastError("Amount and reference number are required.");
      return;
    }

    setIsSubmitting(true);
    try {
      await create({
        amount,
        currency: "UGX",
        reference_number: reference,
        notes,
        receipt: file || undefined,
      });
      setAmount("");
      setReference("");
      setNotes("");
      setFile(null);
      toastSuccess("Payment proof submitted.");
    } catch (err) {
      toastError(err instanceof Error ? err.message : "Submission failed.");
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl font-extrabold text-[#0a1628]">Payments</h1>
        <p className="text-[#64748b]">Upload proof of payment and view history.</p>
      </div>

      <form onSubmit={handleSubmit} className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 space-y-5">
        <h2 className="text-lg font-bold text-[#0a1628]">Submit Payment Proof</h2>
        <div className="grid sm:grid-cols-2 gap-5">
          <InputField
            id="amount"
            label="Amount (UGX)"
            type="number"
            value={amount}
            onChange={(e) => setAmount(e.target.value)}
            required
          />
          <InputField
            id="reference"
            label="Reference Number"
            value={reference}
            onChange={(e) => setReference(e.target.value)}
            required
          />
        </div>
        <TextareaField
          id="payment-notes"
          label="Notes"
          value={notes}
          onChange={(e) => setNotes(e.target.value)}
          placeholder="Bank name, transaction date, etc."
          rows={3}
        />
        <div>
          <label className="block text-sm font-semibold text-[#0a1628] mb-1.5">Receipt</label>
          <div
            onClick={() => document.getElementById("payment-receipt")?.click()}
            className="cursor-pointer border-2 border-dashed border-[#e2e8f0] rounded-xl p-6 hover:border-green-500 hover:bg-green-50/30 transition-colors"
          >
            <div className="flex flex-col items-center text-center">
              <Upload className="w-8 h-8 text-[#94a3b8] mb-2" />
              <p className="text-sm font-medium text-[#0a1628]">{file ? file.name : "Click to upload receipt"}</p>
            </div>
            <input
              id="payment-receipt"
              type="file"
              accept=".pdf,.jpg,.jpeg,.png"
              className="hidden"
              onChange={(e) => setFile(e.target.files?.[0] || null)}
            />
          </div>
        </div>
        <button
          type="submit"
          disabled={isSubmitting}
          className="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 disabled:opacity-60 transition-colors"
        >
          {isSubmitting && <Loader2 className="w-4 h-4 animate-spin" />}
          {isSubmitting ? "Submitting..." : "Submit Payment"}
        </button>
      </form>

      {isLoading ? (
        <div className="min-h-[200px] flex items-center justify-center">
          <Loader2 className="w-8 h-8 animate-spin text-green-500" />
        </div>
      ) : !payments || payments.length === 0 ? (
        <EmptyState title="No payment uploads" description="Your payment submissions will appear here." />
      ) : (
        <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead className="bg-[#f8fafc]">
                <tr>
                  <th className="px-6 py-4 font-semibold text-[#0a1628]">Reference</th>
                  <th className="px-6 py-4 font-semibold text-[#0a1628]">Amount</th>
                  <th className="px-6 py-4 font-semibold text-[#0a1628]">Status</th>
                  <th className="px-6 py-4 font-semibold text-[#0a1628]">Date</th>
                  <th className="px-6 py-4 text-right">Receipt</th>
                </tr>
              </thead>
              <tbody>
                {payments.map((payment) => (
                  <tr key={payment.id} className="border-t border-[#f1f5f9]">
                    <td className="px-6 py-4 font-medium text-[#0a1628]">{payment.reference_number}</td>
                    <td className="px-6 py-4 text-[#64748b]">UGX {payment.amount}</td>
                    <td className="px-6 py-4">
                      <span
                        className={`inline-block px-2.5 py-0.5 rounded-full text-xs font-medium capitalize ${
                          payment.status === "verified"
                            ? "bg-green-100 text-green-700"
                            : payment.status === "rejected"
                            ? "bg-red-100 text-red-700"
                            : "bg-amber-100 text-amber-700"
                        }`}
                      >
                        {payment.status}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-[#64748b]">{new Date(payment.created_at).toLocaleDateString()}</td>
                    <td className="px-6 py-4 text-right">
                      {payment.file_url && (
                        <a
                          href={payment.file_url}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="inline-flex items-center gap-1.5 text-xs font-semibold text-green-700 bg-green-50 px-3 py-1.5 rounded-lg hover:bg-green-100"
                        >
                          <FileText className="w-3 h-3" />
                          View
                        </a>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  );
}
