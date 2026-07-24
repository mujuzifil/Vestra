import { Skeleton } from "./skeleton";

export function SkeletonCard() {
  return (
    <div className="group bg-surface-card rounded-[20px] overflow-hidden border border-border-default shadow-sm flex flex-col animate-pulse">
      <div className="relative p-6 lg:p-8 min-h-[240px] lg:min-h-[260px] flex items-center justify-center bg-gradient-to-b from-neutral-50 to-white">
        <Skeleton variant="circle" className="w-32 h-32" />
      </div>
      <div className="p-6 flex-1 flex flex-col gap-2">
        <Skeleton className="h-4 w-20" />
        <Skeleton className="h-6 w-3/4" />
        <Skeleton className="h-4 w-full" />
        <Skeleton className="h-4 w-2/3 flex-1" />
        <Skeleton className="h-8 w-1/3" />
        <Skeleton className="h-10 w-full rounded-full" />
      </div>
    </div>
  );
}
