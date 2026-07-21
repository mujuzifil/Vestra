export function SkeletonCard() {
  return (
    <div className="group bg-white rounded-[20px] overflow-hidden border border-[#e2e8f0] shadow-sm flex flex-col animate-pulse">
      <div className="relative p-6 lg:p-8 min-h-[240px] lg:min-h-[260px] flex items-center justify-center bg-gradient-to-b from-[#f8fafc] to-white">
        <div className="w-32 h-32 rounded-full bg-[#e2e8f0]" />
      </div>
      <div className="p-6 flex-1 flex flex-col">
        <div className="h-4 w-20 rounded bg-[#e2e8f0] mb-2" />
        <div className="h-6 w-3/4 rounded bg-[#e2e8f0] mb-2" />
        <div className="h-4 w-full rounded bg-[#e2e8f0] mb-2" />
        <div className="h-4 w-2/3 rounded bg-[#e2e8f0] mb-4 flex-1" />
        <div className="h-8 w-1/3 rounded bg-[#e2e8f0] mb-4" />
        <div className="h-10 w-full rounded-full bg-[#e2e8f0]" />
      </div>
    </div>
  );
}
