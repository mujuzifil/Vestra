import { cn } from "@/lib/utils";

interface DistributorStatCardProps {
  label: string;
  value: string | number;
  icon: React.ElementType;
  color?: string;
  className?: string;
}

export function DistributorStatCard({
  label,
  value,
  icon: Icon,
  color = "bg-[#0a1628]",
  className,
}: DistributorStatCardProps) {
  return (
    <div className={cn("bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-5", className)}>
      <div className="flex items-start justify-between">
        <div>
          <p className="text-sm text-[#64748b]">{label}</p>
          <p className="text-2xl lg:text-3xl font-extrabold text-[#0a1628] mt-1">{value}</p>
        </div>
        <div className={cn("p-2.5 rounded-xl", color)}>
          <Icon className="w-5 h-5 text-white" />
        </div>
      </div>
    </div>
  );
}
