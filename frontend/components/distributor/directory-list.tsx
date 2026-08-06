"use client";

import { useEffect, useState } from "react";
import { Search, MapPin, Phone, Mail, Clock, Loader2, Store } from "lucide-react";
import { InputField } from "@/components/common/form-field";
import { getPublicDistributors } from "@/lib/api/public-distributors";
import { cn } from "@/lib/utils";
import type { PublicDistributor } from "@/types";

interface DirectoryListProps {
  contactPhone: string;
  contactEmail: string;
}

export function DirectoryList({ contactPhone, contactEmail }: DirectoryListProps) {
  const [distributors, setDistributors] = useState<PublicDistributor[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState("");
  const [district, setDistrict] = useState("");
  const [region, setRegion] = useState("");

  useEffect(() => {
    const timeout = setTimeout(() => {
      setLoading(true);
      getPublicDistributors({ search, district, region })
        .then((data) => setDistributors(data))
        .catch(() => setDistributors([]))
        .finally(() => setLoading(false));
    }, 300);

    return () => clearTimeout(timeout);
  }, [search, district, region]);

  return (
    <div className="space-y-8">
      <div className="grid sm:grid-cols-3 gap-4">
        <div className="relative">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted" />
          <input
            type="text"
            placeholder="Search by business name"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full pl-10 pr-4 py-3 rounded-xl border border-border-default bg-white text-text-heading placeholder:text-text-placeholder focus:outline-none focus:ring-2 focus:ring-secondary-500"
          />
        </div>
        <InputField
          id="directory-district"
          name="directory-district"
          label=""
          placeholder="Filter by district"
          value={district}
          onChange={(e) => setDistrict(e.target.value)}
          className="py-3"
        />
        <InputField
          id="directory-region"
          name="directory-region"
          label=""
          placeholder="Filter by region"
          value={region}
          onChange={(e) => setRegion(e.target.value)}
          className="py-3"
        />
      </div>

      {loading ? (
        <div className="flex items-center justify-center py-16">
          <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
        </div>
      ) : distributors.length === 0 ? (
        <div className="text-center py-16 px-6 rounded-[24px] bg-surface-card border border-border">
          <div className="w-16 h-16 rounded-full bg-secondary-500/10 flex items-center justify-center mx-auto mb-4">
            <Store className="w-8 h-8 text-secondary-600" />
          </div>
          <h3 className="text-xl font-bold text-text-heading mb-2">No distributors found</h3>
          <p className="text-text-muted max-w-md mx-auto mb-6">
            We don&apos;t have an authorised partner matching your search yet. Our sales team can connect you with the nearest distributor or arrange direct supply.
          </p>
          <div className="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a
              href={`tel:${contactPhone.replace(/\s/g, "")}`}
              className="inline-flex items-center gap-2 px-6 py-3 rounded-full font-semibold text-white bg-gradient-to-br from-secondary-500 to-secondary-600 shadow-lg"
            >
              <Phone className="w-4 h-4" />
              Call Sales
            </a>
            <a
              href={`mailto:${contactEmail}`}
              className="inline-flex items-center gap-2 px-6 py-3 rounded-full font-semibold text-text-heading bg-white border border-default"
            >
              <Mail className="w-4 h-4" />
              Email Sales
            </a>
          </div>
        </div>
      ) : (
        <div className="grid md:grid-cols-2 gap-6">
          {distributors.map((distributor) => (
            <div
              key={distributor.id}
              className="p-6 rounded-[20px] bg-white border border-border shadow-sm hover:shadow-md transition-shadow"
            >
              <div className="flex items-start justify-between gap-4 mb-4">
                <div>
                  <h3 className="text-lg font-bold text-text-heading">
                    {distributor.trading_name || distributor.company_name}
                  </h3>
                  {distributor.trading_name && (
                    <p className="text-sm text-text-muted">{distributor.company_name}</p>
                  )}
                  {distributor.business_type && (
                    <span className="inline-block mt-2 px-2.5 py-1 rounded-full text-xs font-medium bg-primary-50 text-primary-700">
                      {distributor.business_type}
                    </span>
                  )}
                </div>
              </div>

              <div className="space-y-2 text-sm">
                {distributor.branch?.formatted_address && (
                  <div className="flex items-start gap-3 text-text-body">
                    <MapPin className="w-4 h-4 text-secondary-500 mt-0.5 flex-shrink-0" />
                    <span>{distributor.branch.formatted_address}</span>
                  </div>
                )}
                {(distributor.phone || distributor.branch?.phone) && (
                  <div className="flex items-center gap-3 text-text-body">
                    <Phone className="w-4 h-4 text-secondary-500 flex-shrink-0" />
                    <a
                      href={`tel:${(distributor.phone || distributor.branch?.phone || "").replace(/\s/g, "")}`}
                      className="hover:text-secondary-600"
                    >
                      {distributor.phone || distributor.branch?.phone}
                    </a>
                  </div>
                )}
                {(distributor.email || distributor.branch?.email) && (
                  <div className="flex items-center gap-3 text-text-body">
                    <Mail className="w-4 h-4 text-secondary-500 flex-shrink-0" />
                    <a
                      href={`mailto:${distributor.email || distributor.branch?.email}`}
                      className="hover:text-secondary-600"
                    >
                      {distributor.email || distributor.branch?.email}
                    </a>
                  </div>
                )}
                {distributor.operating_hours && (
                  <div className="flex items-start gap-3 text-text-body">
                    <Clock className="w-4 h-4 text-secondary-500 mt-0.5 flex-shrink-0" />
                    <span>
                      {typeof distributor.operating_hours === "string"
                        ? distributor.operating_hours
                        : Object.entries(distributor.operating_hours)
                            .map(([day, hours]) => `${day}: ${String(hours)}`)
                            .join(" · ")}
                    </span>
                  </div>
                )}
              </div>

              {distributor.service_areas && distributor.service_areas.length > 0 && (
                <div className="mt-4 pt-4 border-t border-border">
                  <p className="text-xs font-semibold uppercase tracking-wider text-text-muted mb-2">
                    Service Areas
                  </p>
                  <div className="flex flex-wrap gap-2">
                    {distributor.service_areas.slice(0, 6).map((area) => (
                      <span
                        key={`${area.region}-${area.district}`}
                        className={cn(
                          "px-2 py-1 rounded-lg text-xs font-medium",
                          area.status === "covered"
                            ? "bg-green-50 text-green-700"
                            : area.status === "coming_soon"
                            ? "bg-amber-50 text-amber-700"
                            : "bg-neutral-100 text-text-body"
                        )}
                      >
                        {area.district}
                      </span>
                    ))}
                    {distributor.service_areas.length > 6 && (
                      <span className="px-2 py-1 rounded-lg text-xs font-medium bg-neutral-100 text-text-body">
                        +{distributor.service_areas.length - 6} more
                      </span>
                    )}
                  </div>
                </div>
              )}
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
