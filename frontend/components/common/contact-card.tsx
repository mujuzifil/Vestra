"use client";

import { Icon } from "@/components/common/icon";
import { cn } from "@/lib/utils";

interface ContactCardProps {
  icon: string;
  title: string;
  lines: { label?: string; value: string; href?: string }[];
  className?: string;
}

export function ContactCard({ icon, title, lines, className }: ContactCardProps) {
  return (
    <div
      className={cn(
        "p-6 lg:p-8 rounded-[20px] bg-surface-card border border-border shadow-sm hover:-translate-y-1 hover:shadow-md transition-all-base",
        className
      )}
    >
      <div className="w-14 h-14 rounded-full bg-gradient-to-br from-secondary-500 to-secondary-600 flex items-center justify-center text-white mb-5 shadow-lg shadow-secondary-500/25">
        <Icon name={icon} className="w-6 h-6" />
      </div>
      <h3 className="text-lg font-bold text-text-heading mb-3">{title}</h3>
      <ul className="space-y-2">
        {lines.map((line, index) => (
          <li key={index} className="text-text-body">
            {line.href ? (
              <a
                href={line.href}
                className="hover:text-secondary-600 transition-colors-base"
                target={line.href.startsWith("http") ? "_blank" : undefined}
                rel={line.href.startsWith("http") ? "noopener noreferrer" : undefined}
              >
                {line.label && <span className="block text-xs text-text-muted">{line.label}</span>}
                {line.value}
              </a>
            ) : (
              <>
                {line.label && <span className="block text-xs text-text-muted">{line.label}</span>}
                {line.value}
              </>
            )}
          </li>
        ))}
      </ul>
    </div>
  );
}
