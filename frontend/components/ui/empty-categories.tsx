import { FolderOpen } from "lucide-react";

interface EmptyCategoriesProps {
  title?: string;
  description?: string;
}

export function EmptyCategories({
  title = "No categories available",
  description = "Categories will appear here once they are added.",
}: EmptyCategoriesProps) {
  return (
    <div className="flex flex-col items-center justify-center py-12 px-4 text-center">
      <FolderOpen className="w-10 h-10 text-[#94a3b8] mb-3" aria-hidden="true" />
      <h3 className="text-base font-bold text-[#0a1628] mb-1">{title}</h3>
      <p className="text-[#64748b] text-sm max-w-md">{description}</p>
    </div>
  );
}
