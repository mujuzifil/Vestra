import { cn } from "@/lib/utils";

interface QuoteStatusBadgeProps {
  status: string;
  className?: string;
}

const statusStyles: Record<string, string> = {
  draft: "bg-slate-100 text-slate-700",
  submitted: "bg-amber-100 text-amber-700",
  reviewed: "bg-blue-100 text-blue-700",
  quoted: "bg-indigo-100 text-indigo-700",
  accepted: "bg-emerald-100 text-emerald-700",
  rejected: "bg-red-100 text-red-700",
  converted_to_order: "bg-green-100 text-green-700",
};

export function QuoteStatusBadge({ status, className }: QuoteStatusBadgeProps) {
  const label = status.replace(/_/g, " ");
  return (
    <span
      className={cn(
        "inline-block px-2.5 py-0.5 rounded-full text-xs font-medium capitalize",
        statusStyles[status] || "bg-gray-100 text-gray-700",
        className
      )}
    >
      {label}
    </span>
  );
}
