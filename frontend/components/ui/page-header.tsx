import * as React from "react";
import { cn } from "@/lib/utils";
import { Breadcrumb, BreadcrumbItem } from "./breadcrumb";

interface PageHeaderProps extends React.HTMLAttributes<HTMLDivElement> {
  title: string;
  description?: string;
  breadcrumbs?: BreadcrumbItem[];
  actions?: React.ReactNode;
}

function PageHeader({
  className,
  title,
  description,
  breadcrumbs,
  actions,
  ...props
}: PageHeaderProps) {
  return (
    <div
      className={cn(
        "mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between",
        className
      )}
      {...props}
    >
      <div className="space-y-2">
        {breadcrumbs && <Breadcrumb items={breadcrumbs} />}
        <div>
          <h1 className="text-2xl font-bold tracking-tight text-text-heading">
            {title}
          </h1>
          {description && (
            <p className="mt-1 text-sm text-text-muted">{description}</p>
          )}
        </div>
      </div>
      {actions && (
        <div className="flex items-center gap-3 shrink-0">{actions}</div>
      )}
    </div>
  );
}

export { PageHeader };
