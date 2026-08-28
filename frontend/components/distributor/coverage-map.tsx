"use client";

import { useEffect, useMemo, useState } from "react";
import { Loader2, MapPin } from "lucide-react";
import { getCoverageRegions } from "@/lib/api/public-distributors";
import { cn } from "@/lib/utils";
import type { CoverageRegions, CoverageDistrict } from "@/types";

const ugandaRegions = ["Central", "Eastern", "Northern", "Western"] as const;

const statusLabel: Record<string, string> = {
  covered: "Covered",
  coming_soon: "Coming soon",
  planned: "Planned",
};

const regionBlurb: Record<(typeof ugandaRegions)[number], string> = {
  Central: "Greater Kampala & central corridor",
  Eastern: "Jinja, Mbale & eastern towns",
  Northern: "Gulu, Lira & northern towns",
  Western: "Mbarara, Fort Portal & west",
};

export function CoverageMap() {
  const [coverage, setCoverage] = useState<CoverageRegions>({});
  const [loading, setLoading] = useState(true);
  const [activeRegion, setActiveRegion] = useState<(typeof ugandaRegions)[number]>("Central");

  useEffect(() => {
    getCoverageRegions()
      .then((data) => {
        setCoverage(data);
        const firstWithCoverage = ugandaRegions.find((region) => (data[region] || []).length > 0);
        if (firstWithCoverage) {
          setActiveRegion(firstWithCoverage);
        }
      })
      .catch(() => setCoverage({}))
      .finally(() => setLoading(false));
  }, []);

  const summary = useMemo(() => {
    const allDistricts = ugandaRegions.flatMap((region) => coverage[region] || []);
    const covered = allDistricts.filter((d) => d.status === "covered");
    const uniqueDistricts = new Set(covered.map((d) => d.district.toLowerCase()));
    const partnerSignals = covered.reduce((sum, d) => sum + (d.count || 0), 0);
    const activeRegions = ugandaRegions.filter((region) => (coverage[region] || []).some((d) => d.status === "covered"));

    return {
      districtCount: uniqueDistricts.size,
      partnerSignals,
      activeRegionCount: activeRegions.length,
      hasCoverage: uniqueDistricts.size > 0,
    };
  }, [coverage]);

  const activeDistricts: CoverageDistrict[] = coverage[activeRegion] || [];

  if (loading) {
    return (
      <div className="flex items-center justify-center py-16">
        <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
      </div>
    );
  }

  if (!summary.hasCoverage) {
    return (
      <div className="text-center py-12 px-6 rounded-[20px] bg-surface-card border border-border">
        <MapPin className="w-10 h-10 text-secondary-500 mx-auto mb-3" />
        <h3 className="text-lg font-bold text-text-heading mb-1">Coverage map coming soon</h3>
        <p className="text-text-muted">
          Our team is mapping authorised distributor coverage across Uganda. Check back soon or contact sales for current availability.
        </p>
      </div>
    );
  }

  return (
    <div className="min-w-0 space-y-8 overflow-x-clip">
      <div className="grid gap-3 sm:grid-cols-3">
        <div className="rounded-2xl border border-border bg-primary-50/40 px-5 py-4">
          <p className="text-xs font-semibold uppercase tracking-wider text-primary-700">Districts covered</p>
          <p className="mt-1 text-3xl font-extrabold text-text-heading">{summary.districtCount}</p>
        </div>
        <div className="rounded-2xl border border-border bg-secondary-50/50 px-5 py-4">
          <p className="text-xs font-semibold uppercase tracking-wider text-secondary-700">Regions active</p>
          <p className="mt-1 text-3xl font-extrabold text-text-heading">{summary.activeRegionCount} / 4</p>
        </div>
        <div className="rounded-2xl border border-border bg-white px-5 py-4">
          <p className="text-xs font-semibold uppercase tracking-wider text-text-muted">Partner presence</p>
          <p className="mt-1 text-3xl font-extrabold text-text-heading">{summary.partnerSignals}</p>
        </div>
      </div>

      <div className="grid min-w-0 gap-8 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)] items-start">
        <div
          className="relative min-w-0 w-full overflow-hidden rounded-[28px] border border-border bg-[linear-gradient(160deg,#f4f8f3_0%,#eef4f8_45%,#f7faf7_100%)] p-4 sm:p-7"
          aria-label="Uganda regional coverage overview"
        >
          <div className="absolute inset-0 opacity-[0.35]" style={{
            backgroundImage:
              "radial-gradient(circle at 20% 20%, rgba(13,59,102,0.08), transparent 40%), radial-gradient(circle at 80% 70%, rgba(45,138,90,0.12), transparent 35%)",
          }} />

          <div className="relative mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between sm:gap-3">
            <div className="min-w-0">
              <p className="text-xs font-semibold uppercase tracking-[0.18em] text-primary-700">Uganda</p>
              <h3 className="text-xl font-bold text-text-heading">Regional presence</h3>
            </div>
            <p className="text-xs text-text-muted sm:max-w-[12rem] sm:text-right">
              Select a region to see authorised districts.
            </p>
          </div>

          <div className="relative mx-auto grid w-full min-w-0 max-w-md grid-cols-2 gap-3 overflow-x-clip sm:gap-4">
            <button
              type="button"
              onClick={() => setActiveRegion("Northern")}
              className={cn(
                "col-span-2 min-h-[88px] rounded-[22px] border px-4 py-4 text-left transition-all",
                regionTone(coverage, "Northern", activeRegion === "Northern")
              )}
            >
              <RegionTileLabel region="Northern" districts={coverage.Northern || []} />
            </button>

            <button
              type="button"
              onClick={() => setActiveRegion("Western")}
              className={cn(
                "min-h-[120px] rounded-[22px] border px-4 py-4 text-left transition-all",
                regionTone(coverage, "Western", activeRegion === "Western")
              )}
            >
              <RegionTileLabel region="Western" districts={coverage.Western || []} />
            </button>

            <button
              type="button"
              onClick={() => setActiveRegion("Eastern")}
              className={cn(
                "min-h-[120px] rounded-[22px] border px-4 py-4 text-left transition-all",
                regionTone(coverage, "Eastern", activeRegion === "Eastern")
              )}
            >
              <RegionTileLabel region="Eastern" districts={coverage.Eastern || []} />
            </button>

            <button
              type="button"
              onClick={() => setActiveRegion("Central")}
              className={cn(
                "col-span-2 min-h-[100px] rounded-[22px] border px-4 py-4 text-left transition-all",
                regionTone(coverage, "Central", activeRegion === "Central")
              )}
            >
              <RegionTileLabel region="Central" districts={coverage.Central || []} />
            </button>
          </div>
        </div>

        <div className="min-w-0 rounded-[28px] border border-border bg-white p-5 sm:p-7 shadow-sm">
          <div className="mb-5">
            <p className="text-xs font-semibold uppercase tracking-wider text-secondary-600">{activeRegion} region</p>
            <h3 className="text-2xl font-bold text-text-heading mt-1">{activeRegion}</h3>
            <p className="text-sm text-text-muted mt-1">{regionBlurb[activeRegion]}</p>
          </div>

          {activeDistricts.length === 0 ? (
            <div className="rounded-2xl border border-dashed border-border bg-neutral-50 px-4 py-8 text-center">
              <MapPin className="mx-auto mb-2 h-6 w-6 text-text-muted" />
              <p className="font-semibold text-text-heading">No authorised coverage yet</p>
              <p className="mt-1 text-sm text-text-muted">
                We have not listed an authorised distributor district in this region yet.
              </p>
            </div>
          ) : (
            <ul className="space-y-3">
              {activeDistricts.map((district) => (
                <li
                  key={`${district.district}-${district.status}`}
                  className="flex items-center justify-between gap-3 rounded-2xl border border-border bg-surface-page/60 px-4 py-3"
                >
                  <div className="min-w-0">
                    <p className="font-semibold text-text-heading truncate">{district.district}</p>
                    <p className="text-xs text-text-muted">
                      {district.count > 1 ? `${district.count} partner locations` : "Authorised partner location"}
                    </p>
                  </div>
                  <span
                    className={cn(
                      "shrink-0 px-2.5 py-1 rounded-full text-xs font-semibold",
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

          <div className="mt-6 flex flex-wrap gap-3 text-xs text-text-muted">
            <span className="inline-flex items-center gap-1.5">
              <span className="h-2.5 w-2.5 rounded-full bg-green-600" /> Covered
            </span>
            <span className="inline-flex items-center gap-1.5">
              <span className="h-2.5 w-2.5 rounded-full bg-amber-500" /> Coming soon
            </span>
            <span className="inline-flex items-center gap-1.5">
              <span className="h-2.5 w-2.5 rounded-full bg-neutral-300" /> Not listed yet
            </span>
          </div>
        </div>
      </div>
    </div>
  );
}

function regionTone(
  coverage: CoverageRegions,
  region: (typeof ugandaRegions)[number],
  active: boolean
): string {
  const hasCoverage = (coverage[region] || []).some((d) => d.status === "covered");

  if (active && hasCoverage) {
    return "border-secondary-500 bg-secondary-500 text-white shadow-md ring-2 ring-secondary-500/25";
  }
  if (active) {
    return "border-primary-500 bg-white text-text-heading shadow-md ring-2 ring-primary-500/20";
  }
  if (hasCoverage) {
    return "border-secondary-200 bg-secondary-50 text-secondary-900 hover:border-secondary-400";
  }
  return "border-border/80 bg-white/70 text-text-muted hover:border-primary-200";
}

function RegionTileLabel({
  region,
  districts,
}: {
  region: (typeof ugandaRegions)[number];
  districts: CoverageDistrict[];
}) {
  const coveredCount = districts.filter((d) => d.status === "covered").length;

  return (
    <div className="min-w-0">
      <div className="flex min-w-0 items-center justify-between gap-2">
        <span className="min-w-0 truncate text-sm font-bold tracking-wide">{region}</span>
        {coveredCount > 0 ? (
          <span className="shrink-0 text-[11px] font-semibold uppercase tracking-wide opacity-90">
            {coveredCount} district{coveredCount === 1 ? "" : "s"}
          </span>
        ) : (
          <span className="shrink-0 text-[11px] font-medium uppercase tracking-wide opacity-70">Soon</span>
        )}
      </div>
      <p className="mt-1 text-xs opacity-80 line-clamp-2 break-words">{regionBlurb[region]}</p>
    </div>
  );
}
