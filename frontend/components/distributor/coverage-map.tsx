"use client";

import { useEffect, useState } from "react";
import { Loader2, MapPin } from "lucide-react";
import { getCoverageRegions } from "@/lib/api/public-distributors";
import { cn } from "@/lib/utils";
import type { CoverageRegions, CoverageDistrict } from "@/types";

const ugandaRegions = [
  "Central",
  "Eastern",
  "Northern",
  "Western",
];

const statusLabel: Record<string, string> = {
  covered: "Covered",
  coming_soon: "Coming Soon",
  planned: "Planned",
};

export function CoverageMap() {
  const [coverage, setCoverage] = useState<CoverageRegions>({});
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getCoverageRegions()
      .then((data) => setCoverage(data))
      .catch(() => setCoverage({}))
      .finally(() => setLoading(false));
  }, []);

  if (loading) {
    return (
      <div className="flex items-center justify-center py-16">
        <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
      </div>
    );
  }

  const hasCoverage = Object.keys(coverage).length > 0;

  return (
    <div className="space-y-8">
      {!hasCoverage ? (
        <div className="text-center py-12 px-6 rounded-[20px] bg-surface-card border border-border">
          <MapPin className="w-10 h-10 text-secondary-500 mx-auto mb-3" />
          <h3 className="text-lg font-bold text-text-heading mb-1">Coverage map coming soon</h3>
          <p className="text-text-muted">
            Our team is mapping authorised distributor coverage across Uganda. Check back soon or contact sales for current availability.
          </p>
        </div>
      ) : (
        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {ugandaRegions.map((region) => {
            const districts = coverage[region] || [];
            return (
              <div
                key={region}
                className="p-5 rounded-[20px] bg-white border border-border shadow-sm"
              >
                <h3 className="text-lg font-bold text-text-heading mb-4">{region}</h3>
                {districts.length === 0 ? (
                  <p className="text-sm text-text-muted">Coverage to be confirmed.</p>
                ) : (
                  <ul className="space-y-2">
                    {districts.map((district: CoverageDistrict) => (
                      <li key={district.district} className="flex items-center justify-between text-sm">
                        <span className="text-text-body">{district.district}</span>
                        <span
                          className={cn(
                            "px-2 py-0.5 rounded-full text-xs font-medium",
                            district.status === "covered"
                              ? "bg-green-50 text-green-700"
                              : district.status === "coming_soon"
                              ? "bg-amber-50 text-amber-700"
                              : "bg-neutral-100 text-text-body"
                          )}
                        >
                          {statusLabel[district.status] || district.status}
                        </span>
                      </li>
                    ))}
                  </ul>
                )}
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}
