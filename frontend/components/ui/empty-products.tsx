import { PackageX } from "lucide-react";

interface EmptyProductsProps {
  title?: string;
  description?: string;
}

export function EmptyProducts({
  title = "No products found",
  description = "Try adjusting your search or filter to find what you're looking for.",
}: EmptyProductsProps) {
  return (
    <div className="flex flex-col items-center justify-center py-16 px-4 text-center">
      <PackageX className="w-12 h-12 text-neutral-400 mb-4" aria-hidden="true" />
      <h3 className="text-lg font-bold text-primary-900 mb-2">{title}</h3>
      <p className="text-muted max-w-md">{description}</p>
    </div>
  );
}
