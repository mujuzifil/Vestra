import { CreditCard, TrendingUp, AlertCircle } from "lucide-react";
import type { CreditAccountSummary } from "@/types";

interface CreditLimitCardProps {
  credit?: CreditAccountSummary | null;
}

export function CreditLimitCard({ credit }: CreditLimitCardProps) {
  const limit = Number(credit?.limit ?? 0);
  const available = Number(credit?.available_credit ?? 0);
  const utilized = limit > 0 ? Math.min(100, ((limit - available) / limit) * 100) : 0;

  return (
    <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
      <div className="flex items-center gap-3 mb-4">
        <div className="p-2.5 rounded-xl bg-green-600">
          <CreditCard className="w-5 h-5 text-white" />
        </div>
        <div>
          <h3 className="text-lg font-bold text-[#0a1628]">Credit Account</h3>
          <p className="text-sm text-[#64748b]">Current balance and limit</p>
        </div>
      </div>

      {!credit ? (
        <div className="flex items-start gap-3 p-4 rounded-xl bg-amber-50 text-amber-800 text-sm">
          <AlertCircle className="w-5 h-5 flex-shrink-0" />
          <p>Your distributor account does not have a credit account set up yet.</p>
        </div>
      ) : (
        <>
          <div className="grid grid-cols-2 gap-4 mb-4">
            <div className="p-4 rounded-xl bg-[#f8fafc]">
              <p className="text-sm text-[#64748b] mb-1">Credit Limit</p>
              <p className="text-xl font-extrabold text-[#0a1628]">UGX {limit.toLocaleString()}</p>
            </div>
            <div className="p-4 rounded-xl bg-[#f8fafc]">
              <p className="text-sm text-[#64748b] mb-1">Available</p>
              <p className="text-xl font-extrabold text-green-600">UGX {available.toLocaleString()}</p>
            </div>
          </div>

          <div className="space-y-2">
            <div className="flex items-center justify-between text-sm">
              <span className="font-medium text-[#0a1628]">Utilization</span>
              <span className="font-bold text-[#0a1628]">{utilized.toFixed(1)}%</span>
            </div>
            <div className="h-2.5 bg-[#f1f5f9] rounded-full overflow-hidden">
              <div
                className="h-full bg-gradient-to-r from-green-500 to-green-600 rounded-full transition-all"
                style={{ width: `${utilized}%` }}
              />
            </div>
            <div className="flex items-center gap-1.5 text-xs text-[#64748b]">
              <TrendingUp className="w-3.5 h-3.5" />
              <span>{credit.status === "active" ? "Credit line is active" : `Status: ${credit.status}`}</span>
            </div>
          </div>
        </>
      )}
    </div>
  );
}
