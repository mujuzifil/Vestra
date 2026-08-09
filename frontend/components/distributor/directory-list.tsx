"use client";

import { useEffect, useState } from "react";
import {
  Search,
  MapPin,
  Phone,
  Mail,
  Clock,
  Loader2,
  Store,
  ExternalLink,
  MessageCircle,
  Package,
  ShieldCheck,
} from "lucide-react";
import { InputField } from "@/components/common/form-field";
import { DistributorTierBadge } from "@/components/distributor/distributor-tier-badge";
import { getPublicDistributors } from "@/lib/api/public-distributors";
import { cn } from "@/lib/utils";
import type { PublicDistributor } from "@/types";

interface DirectoryListProps {
  contactPhone: string;
  contactEmail: string;
}

type TierFilter = "" | "silver" | "gold" | "master";
type StockFilter = "" | "in_stock" | "low_stock" | "out_of_stock";

function formatOperatingHours(hours: PublicDistributor["operating_hours"]): string | null {
  if (!hours) return null;
  if (typeof hours === "string") return hours;
  const entries = Object.entries(hours);
  if (entries.length === 0) return null;
  return entries.map(([day, value]) => `${day}: ${String(value)}`).join(" · ");
}

function whatsappHref(whatsapp: string): string {
  const digits = whatsapp.replace(/[^\d+]/g, "").replace(/^\+/, "");
  return `https://wa.me/${digits}`;
}

function stockBadgeClass(stock: PublicDistributor["stock_availability"]): string {
  switch (stock) {
    case "low_stock":
      return "bg-amber-50 text-amber-800";
    case "out_of_stock":
      return "bg-red-50 text-red-700";
    case "in_stock":
    default:
      return "bg-green-50 text-green-700";
  }
}

export function DirectoryList({ contactPhone, contactEmail }: DirectoryListProps) {
  const [distributors, setDistributors] = useState<PublicDistributor[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState("");
  const [district, setDistrict] = useState("");
  const [area, setArea] = useState("");
  const [region, setRegion] = useState("");
  const [tier, setTier] = useState<TierFilter>("");
  const [stockAvailability, setStockAvailability] = useState<StockFilter>("");

  const hasFilters = Boolean(search || district || area || region || tier || stockAvailability);

  const clearFilters = () => {
    setSearch("");
    setDistrict("");
    setArea("");
    setRegion("");
    setTier("");
    setStockAvailability("");
  };

  useEffect(() => {
    const timeout = setTimeout(() => {
      setLoading(true);
      getPublicDistributors({
        search,
        district,
        area,
        region,
        tier,
        stock_availability: stockAvailability,
      })
        .then((data) => setDistributors(data))
        .catch(() => setDistributors([]))
        .finally(() => setLoading(false));
    }, 300);

    return () => clearTimeout(timeout);
  }, [search, district, area, region, tier, stockAvailability]);

  return (
    <div className="space-y-8">
      <div className="rounded-[24px] border border-border bg-gradient-to-br from-white via-white to-secondary-50/40 p-6 shadow-sm">
        <div className="mb-5 flex items-start gap-3">
          <div className="mt-0.5 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-secondary-500/10 text-secondary-700">
            <ShieldCheck className="h-5 w-5" aria-hidden="true" />
          </div>
          <div>
            <h3 className="text-lg font-bold text-text-heading">
              Find an Authorized VESTRA Distributor Near You.
            </h3>
            <p className="mt-1 text-sm text-text-muted">
              Shop with confidence from an Authorized VESTRA Distributor. Every listing is verified by VESTRA.
            </p>
          </div>
        </div>

        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div className="relative sm:col-span-2 lg:col-span-1">
            <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
            <input
              type="text"
              placeholder="Search by business name"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="w-full rounded-xl border border-border-default bg-white py-3 pl-10 pr-4 text-text-heading placeholder:text-text-placeholder focus:outline-none focus:ring-2 focus:ring-secondary-500"
            />
          </div>
          <InputField
            id="directory-district"
            name="directory-district"
            label=""
            placeholder="District"
            value={district}
            onChange={(e) => setDistrict(e.target.value)}
            className="py-3"
          />
          <InputField
            id="directory-area"
            name="directory-area"
            label=""
            placeholder="Area / town"
            value={area}
            onChange={(e) => setArea(e.target.value)}
            className="py-3"
          />
          <InputField
            id="directory-region"
            name="directory-region"
            label=""
            placeholder="Region"
            value={region}
            onChange={(e) => setRegion(e.target.value)}
            className="py-3"
          />
          <div>
            <label htmlFor="directory-tier" className="sr-only">
              Distributor tier
            </label>
            <select
              id="directory-tier"
              value={tier}
              onChange={(e) => setTier(e.target.value as TierFilter)}
              className="w-full rounded-xl border border-border-default bg-white px-4 py-3 text-text-heading focus:outline-none focus:ring-2 focus:ring-secondary-500"
            >
              <option value="">All tiers</option>
              <option value="silver">Silver</option>
              <option value="gold">Gold</option>
              <option value="master">Master</option>
            </select>
          </div>
          <div>
            <label htmlFor="directory-stock" className="sr-only">
              Stock availability
            </label>
            <select
              id="directory-stock"
              value={stockAvailability}
              onChange={(e) => setStockAvailability(e.target.value as StockFilter)}
              className="w-full rounded-xl border border-border-default bg-white px-4 py-3 text-text-heading focus:outline-none focus:ring-2 focus:ring-secondary-500"
            >
              <option value="">All stock levels</option>
              <option value="in_stock">In Stock</option>
              <option value="low_stock">Low Stock</option>
              <option value="out_of_stock">Out of Stock</option>
            </select>
          </div>
        </div>

        {hasFilters && (
          <div className="mt-4">
            <button
              type="button"
              onClick={clearFilters}
              className="text-sm font-semibold text-secondary-700 hover:text-secondary-800"
            >
              Clear filters
            </button>
          </div>
        )}
      </div>

      {loading ? (
        <div className="flex items-center justify-center py-16">
          <Loader2 className="h-8 w-8 animate-spin text-secondary-500" />
        </div>
      ) : distributors.length === 0 ? (
        <div className="rounded-[24px] border border-border bg-surface-card px-6 py-16 text-center">
          <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-secondary-500/10">
            <Store className="h-8 w-8 text-secondary-600" />
          </div>
          <h3 className="mb-2 text-xl font-bold text-text-heading">
            No Authorized VESTRA Distributors found for this location.
          </h3>
          <p className="mx-auto mb-6 max-w-md text-text-muted">
            Try another district or area, clear your filters, or contact VESTRA for help finding an authorized partner near you.
          </p>
          <div className="flex flex-col items-center justify-center gap-3 sm:flex-row">
            {hasFilters && (
              <button
                type="button"
                onClick={clearFilters}
                className="inline-flex items-center gap-2 rounded-full border border-default bg-white px-6 py-3 font-semibold text-text-heading"
              >
                Clear filters
              </button>
            )}
            <a
              href={`tel:${contactPhone.replace(/\s/g, "")}`}
              className="inline-flex items-center gap-2 rounded-full bg-gradient-to-br from-secondary-500 to-secondary-600 px-6 py-3 font-semibold text-white shadow-lg"
            >
              <Phone className="h-4 w-4" />
              Call Sales
            </a>
            <a
              href={`mailto:${contactEmail}`}
              className="inline-flex items-center gap-2 rounded-full border border-default bg-white px-6 py-3 font-semibold text-text-heading"
            >
              <Mail className="h-4 w-4" />
              Email Sales
            </a>
            <a
              href="/contact"
              className="inline-flex items-center gap-2 rounded-full border border-default bg-white px-6 py-3 font-semibold text-text-heading"
            >
              Contact VESTRA
            </a>
          </div>
        </div>
      ) : (
        <div className="grid gap-6 md:grid-cols-2">
          {distributors.map((distributor) => {
            const hours = formatOperatingHours(distributor.operating_hours);
            const phone = distributor.phone || distributor.branch?.phone;
            const mapsUrl = distributor.google_maps_url;
            const locationBits = [
              distributor.district || distributor.branch?.district,
              distributor.city || distributor.area || distributor.branch?.city,
            ].filter(Boolean);

            return (
              <div
                key={distributor.id}
                className="overflow-hidden rounded-[20px] border border-border bg-white shadow-sm transition-shadow hover:shadow-md"
              >
                <div className="border-b border-border bg-neutral-50/80 px-6 py-4">
                  <div className="flex flex-wrap items-start justify-between gap-3">
                    <div className="min-w-0">
                      <h3 className="text-lg font-bold text-text-heading">
                        {distributor.trading_name || distributor.company_name}
                      </h3>
                      {distributor.trading_name && (
                        <p className="text-sm text-text-muted">{distributor.company_name}</p>
                      )}
                    </div>
                    <DistributorTierBadge
                      tier={distributor.tier}
                      label={distributor.tier_label}
                    />
                  </div>
                  {distributor.stock_availability_label && (
                    <div className="mt-3">
                      <span
                        className={cn(
                          "inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium",
                          stockBadgeClass(distributor.stock_availability)
                        )}
                      >
                        <Package className="h-3 w-3" />
                        {distributor.stock_availability_label}
                      </span>
                    </div>
                  )}
                </div>

                <div className="space-y-2 px-6 py-4 text-sm">
                  {locationBits.length > 0 && (
                    <div className="flex items-start gap-3 text-text-body">
                      <MapPin className="mt-0.5 h-4 w-4 flex-shrink-0 text-secondary-500" />
                      <span>
                        <span className="font-medium text-text-heading">District / Area: </span>
                        {locationBits.join(" · ")}
                      </span>
                    </div>
                  )}
                  {distributor.branch?.formatted_address && (
                    <div className="flex items-start gap-3 text-text-body">
                      <MapPin className="mt-0.5 h-4 w-4 flex-shrink-0 text-secondary-500" />
                      <span>{distributor.branch.formatted_address}</span>
                    </div>
                  )}
                  {phone && (
                    <div className="flex items-center gap-3 text-text-body">
                      <Phone className="h-4 w-4 flex-shrink-0 text-secondary-500" />
                      <a href={`tel:${phone.replace(/\s/g, "")}`} className="hover:text-secondary-600">
                        {phone}
                      </a>
                    </div>
                  )}
                  {hours && (
                    <div className="flex items-start gap-3 text-text-body">
                      <Clock className="mt-0.5 h-4 w-4 flex-shrink-0 text-secondary-500" />
                      <span>{hours}</span>
                    </div>
                  )}
                </div>

                <div className="flex flex-wrap gap-2 border-t border-border px-6 py-4">
                  {distributor.whatsapp && (
                    <a
                      href={whatsappHref(distributor.whatsapp)}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                    >
                      <MessageCircle className="h-4 w-4" />
                      WhatsApp
                    </a>
                  )}
                  {mapsUrl && (
                    <a
                      href={mapsUrl}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center gap-2 rounded-full border border-default bg-white px-4 py-2 text-sm font-semibold text-text-heading hover:bg-neutral-50"
                    >
                      <ExternalLink className="h-4 w-4" />
                      View Location
                    </a>
                  )}
                </div>

                {distributor.service_areas && distributor.service_areas.length > 0 && (
                  <div className="border-t border-border px-6 py-4">
                    <p className="mb-2 text-xs font-semibold uppercase tracking-wider text-text-muted">
                      Service Areas
                    </p>
                    <div className="flex flex-wrap gap-2">
                      {distributor.service_areas.slice(0, 6).map((svcArea) => (
                        <span
                          key={`${svcArea.region}-${svcArea.district}`}
                          className={cn(
                            "rounded-lg px-2 py-1 text-xs font-medium",
                            svcArea.status === "covered"
                              ? "bg-green-50 text-green-700"
                              : svcArea.status === "coming_soon"
                                ? "bg-amber-50 text-amber-700"
                                : "bg-neutral-100 text-text-body"
                          )}
                        >
                          {svcArea.district}
                        </span>
                      ))}
                      {distributor.service_areas.length > 6 && (
                        <span className="rounded-lg bg-neutral-100 px-2 py-1 text-xs font-medium text-text-body">
                          +{distributor.service_areas.length - 6} more
                        </span>
                      )}
                    </div>
                  </div>
                )}
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}
