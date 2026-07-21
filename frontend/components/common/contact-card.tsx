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
        "p-6 lg:p-8 rounded-[20px] bg-white border border-[#e2e8f0] shadow-sm hover:-translate-y-1 hover:shadow-md transition-all",
        className
      )}
    >
      <div className="w-14 h-14 rounded-full bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center text-white mb-5 shadow-lg shadow-green-500/25">
        <Icon name={icon} className="w-6 h-6" />
      </div>
      <h3 className="text-lg font-bold text-[#0a1628] mb-3">{title}</h3>
      <ul className="space-y-2">
        {lines.map((line, index) => (
          <li key={index} className="text-[#475569]">
            {line.href ? (
              <a
                href={line.href}
                className="hover:text-green-600 transition-colors"
                target={line.href.startsWith("http") ? "_blank" : undefined}
                rel={line.href.startsWith("http") ? "noopener noreferrer" : undefined}
              >
                {line.label && <span className="block text-xs text-[#94a3b8]">{line.label}</span>}
                {line.value}
              </a>
            ) : (
              <>
                {line.label && <span className="block text-xs text-[#94a3b8]">{line.label}</span>}
                {line.value}
              </>
            )}
          </li>
        ))}
      </ul>
    </div>
  );
}
