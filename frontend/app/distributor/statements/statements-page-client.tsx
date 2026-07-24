"use client";

import { useState } from "react";
import { Loader2, Calendar } from "lucide-react";
import { useDistributorStatement } from "@/hooks/use-distributor-statements";
import { EmptyState } from "@/components/common/empty-state";

export function StatementsPageClient() {
  const [start, setStart] = useState("");
  const [end, setEnd] = useState("");
  const params = start && end ? { start, end } : undefined;
  const { data: statement, isLoading } = useDistributorStatement(params);

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl font-extrabold text-primary-900">Statements</h1>
        <p className="text-muted">Review your account statement and transaction history.</p>
      </div>

      <div className="bg-white rounded-[20px] border border-neutral-200 shadow-sm p-6">
        <div className="flex flex-col sm:flex-row items-end gap-4">
          <div className="flex-1 w-full">
            <label className="block text-sm font-semibold text-primary-900 mb-1.5">Start Date</label>
            <div className="relative">
              <Calendar className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-placeholder" />
              <input
                type="date"
                value={start}
                onChange={(e) => setStart(e.target.value)}
                className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
              />
            </div>
          </div>
          <div className="flex-1 w-full">
            <label className="block text-sm font-semibold text-primary-900 mb-1.5">End Date</label>
            <div className="relative">
              <Calendar className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-placeholder" />
              <input
                type="date"
                value={end}
                onChange={(e) => setEnd(e.target.value)}
                className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
              />
            </div>
          </div>
          <button
            type="button"
            onClick={() => {
              setStart("");
              setEnd("");
            }}
            className="px-5 py-2.5 text-muted font-semibold hover:text-primary-900"
          >
            Reset
          </button>
        </div>
      </div>

      {isLoading ? (
        <div className="min-h-[50vh] flex items-center justify-center">
          <Loader2 className="w-8 h-8 animate-spin text-green-500" />
        </div>
      ) : !statement || statement.transactions.length === 0 ? (
        <EmptyState title="No statement data" description="Select a date range to view your statement." />
      ) : (
        <>
          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div className="bg-white rounded-[20px] border border-neutral-200 shadow-sm p-5">
              <p className="text-sm text-muted mb-1">Opening Balance</p>
              <p className="text-2xl font-extrabold text-primary-900">UGX {statement.opening_balance}</p>
            </div>
            <div className="bg-white rounded-[20px] border border-neutral-200 shadow-sm p-5">
              <p className="text-sm text-muted mb-1">Closing Balance</p>
              <p className="text-2xl font-extrabold text-primary-900">UGX {statement.closing_balance}</p>
            </div>
            <div className="bg-white rounded-[20px] border border-neutral-200 shadow-sm p-5">
              <p className="text-sm text-muted mb-1">Period</p>
              <p className="text-lg font-extrabold text-primary-900">
                {new Date(statement.period_start).toLocaleDateString()} - {new Date(statement.period_end).toLocaleDateString()}
              </p>
            </div>
          </div>

          <div className="bg-white rounded-[20px] border border-neutral-200 shadow-sm overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm">
                <thead className="bg-neutral-50">
                  <tr>
                    <th className="px-6 py-4 font-semibold text-primary-900">Date</th>
                    <th className="px-6 py-4 font-semibold text-primary-900">Description</th>
                    <th className="px-6 py-4 font-semibold text-primary-900">Invoice</th>
                    <th className="px-6 py-4 font-semibold text-primary-900 text-right">Debit</th>
                    <th className="px-6 py-4 font-semibold text-primary-900 text-right">Credit</th>
                    <th className="px-6 py-4 font-semibold text-primary-900 text-right">Balance</th>
                  </tr>
                </thead>
                <tbody>
                  {statement.transactions.map((tx, index) => (
                    <tr key={index} className="border-t border-neutral-100">
                      <td className="px-6 py-4 text-muted">{new Date(tx.date).toLocaleDateString()}</td>
                      <td className="px-6 py-4 text-primary-900">{tx.description}</td>
                      <td className="px-6 py-4 text-muted">{tx.invoice_number || "—"}</td>
                      <td className="px-6 py-4 text-right font-medium text-red-600">{Number(tx.debit) > 0 ? `UGX ${tx.debit}` : "—"}</td>
                      <td className="px-6 py-4 text-right font-medium text-green-600">{Number(tx.credit) > 0 ? `UGX ${tx.credit}` : "—"}</td>
                      <td className="px-6 py-4 text-right font-bold text-primary-900">UGX {tx.balance}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </>
      )}
    </div>
  );
}
