"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import {
  Loader2,
  Building2,
  Award,
  TrendingUp,
  Truck,
  Megaphone,
  Headphones,
  MapPin,
  CheckCircle2,
  Clock,
  XCircle,
  ArrowRight,
  Package,
  FileSpreadsheet,
  CreditCard,
  FolderOpen,
  Mail,
  ShoppingCart,
  Bell,
  AlertCircle,
} from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useDistributorApplicationStatus } from "@/hooks/use-distributor-application-status";
import { useDistributorDashboard } from "@/hooks/use-distributor-dashboard";
import type { DistributorRequest, DistributorDashboard } from "@/types";

const benefits = [
  { icon: Award, title: "Trusted Brand", description: "Represent a recognised professional fabric care name." },
  { icon: TrendingUp, title: "Competitive Margins", description: "Attractive pricing that supports a sustainable business." },
  { icon: Truck, title: "Reliable Supply", description: "Consistent stock availability and dependable logistics." },
  { icon: Megaphone, title: "Marketing Support", description: "Access to branded materials and campaign guidance." },
  { icon: Headphones, title: "Sales Assistance", description: "Dedicated support to help you win and serve customers." },
  { icon: MapPin, title: "Territory Opportunities", description: "Exclusive or preferred distribution areas where available." },
];

function formatDate(value: string | null | undefined): string {
  if (!value) return "—";
  return new Date(value).toLocaleDateString("en-UG", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}

function formatMoney(value: string | number | null | undefined): string {
  if (value === null || value === undefined || value === "") return "—";
  const n = typeof value === "number" ? value : Number(value);
  if (Number.isNaN(n)) return String(value);
  return `UGX ${n.toLocaleString("en-UG")}`;
}

function StatusBadge({ status }: { status: string }) {
  const styles: Record<string, string> = {
    pending: "bg-warning-100 text-warning-600",
    approved: "bg-secondary-100 text-secondary-600",
    active: "bg-secondary-100 text-secondary-600",
    suspended: "bg-danger-100 text-danger-600",
    rejected: "bg-danger-100 text-danger-600",
  };
  return (
    <span
      className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold capitalize ${
        styles[status] || "bg-neutral-100 text-text-body"
      }`}
    >
      {(status === "pending") && <Clock className="w-3.5 h-3.5" />}
      {(status === "approved" || status === "active") && <CheckCircle2 className="w-3.5 h-3.5" />}
      {(status === "rejected" || status === "suspended") && <XCircle className="w-3.5 h-3.5" />}
      {status}
    </span>
  );
}

interface TimelineStep {
  title: string;
  description: string;
  date: string | null | undefined;
  state: "completed" | "current" | "upcoming";
}

function buildTimeline(data: DistributorRequest, status: string): TimelineStep[] {
  return [
    {
      title: "Application Submitted",
      description: "Your distributor application was received.",
      date: data.created_at,
      state: "completed",
    },
    {
      title: "Review in Progress",
      description: "Our team is assessing your business details.",
      date: status === "pending" ? data.updated_at : status === "approved" || status === "rejected" ? data.updated_at : undefined,
      state: status === "pending" ? "current" : "completed",
    },
    {
      title: status === "approved" ? "Approved" : status === "rejected" ? "Not Approved" : "Decision",
      description:
        status === "approved"
          ? "Congratulations, your application has been approved."
          : status === "rejected"
          ? "Your application did not meet our current criteria."
          : "You will be notified once a decision is made.",
      date: status === "approved" || status === "rejected" ? data.updated_at : undefined,
      state: status === "pending" ? "upcoming" : "current",
    },
  ];
}

function Timeline({ steps }: { steps: TimelineStep[] }) {
  return (
    <div className="relative">
      <div className="absolute left-4 top-0 bottom-0 w-0.5 bg-default" />
      <div className="space-y-6">
        {steps.map((step, index) => (
          <div key={index} className="relative flex items-start gap-4">
            <div
              className={`relative z-10 w-8 h-8 rounded-full flex items-center justify-center text-white ${
                step.state === "completed"
                  ? "bg-secondary-500"
                  : step.state === "current"
                  ? "bg-warning-500"
                  : "bg-neutral-300"
              }`}
            >
              {step.state === "completed" ? (
                <CheckCircle2 className="w-4 h-4" />
              ) : step.state === "current" ? (
                <Clock className="w-4 h-4" />
              ) : (
                <div className="w-2 h-2 rounded-full bg-white" />
              )}
            </div>
            <div className="pt-1 flex-1">
              <p className="font-semibold text-text-heading">{step.title}</p>
              <p className="text-sm text-muted">{step.description}</p>
              {step.date && (
                <p className="text-xs text-placeholder mt-1">{formatDate(step.date)}</p>
              )}
            </div>
            {step.state === "current" && (
              <span className="text-xs font-semibold text-warning-600 bg-warning-50 px-2 py-1 rounded-full">
                Current
              </span>
            )}
          </div>
        ))}
      </div>
    </div>
  );
}

function NoApplicationState() {
  return (
    <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
      <div className="text-center mb-8">
        <div className="w-16 h-16 rounded-full bg-secondary-50 flex items-center justify-center mx-auto mb-4">
          <Building2 className="w-8 h-8 text-secondary-600" />
        </div>
        <h2 className="text-xl font-bold text-text-heading mb-2">Become a VESTRA® Distributor</h2>
        <p className="text-muted max-w-xl mx-auto">
          Partner with us to bring professional fabric care solutions to your market.
        </p>
      </div>

      <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        {benefits.map((benefit) => (
          <div key={benefit.title} className="bg-surface-page rounded-xl border border-default p-4">
            <benefit.icon className="w-6 h-6 text-secondary-600 mb-3" />
            <h3 className="font-semibold text-text-heading mb-1">{benefit.title}</h3>
            <p className="text-sm text-muted">{benefit.description}</p>
          </div>
        ))}
      </div>

      <div className="text-center">
        <Link
          href="/distributor"
          className="inline-flex items-center gap-2 px-6 py-3 bg-secondary-600 text-white font-semibold rounded-xl hover:opacity-90"
        >
          Apply Now
          <ArrowRight className="w-4 h-4" />
        </Link>
      </div>
    </div>
  );
}

function PendingState({ data }: { data: DistributorRequest }) {
  const steps = buildTimeline(data, "pending");
  return (
    <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
          <h2 className="text-xl font-bold text-text-heading mb-1">Application Under Review</h2>
          <p className="text-sm text-muted">Reference #{data.id}</p>
        </div>
        <StatusBadge status={data.status} />
      </div>

      <p className="text-body mb-6">
        Your application was submitted on <strong className="text-text-heading">{formatDate(data.created_at)}</strong>.
        Our team is currently reviewing your business information and will contact you once a decision is made.
      </p>

      <div className="bg-surface-page rounded-xl border border-default p-5 mb-6">
        <h3 className="font-semibold text-text-heading mb-4">Application Progress</h3>
        <Timeline steps={steps} />
      </div>

      <Link
        href="/distributor"
        className="inline-flex items-center gap-2 px-6 py-3 bg-secondary-600 text-white font-semibold rounded-xl hover:opacity-90"
      >
        More Information
        <ArrowRight className="w-4 h-4" />
      </Link>
    </div>
  );
}

function RejectedState({ data }: { data: DistributorRequest }) {
  return (
    <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
          <h2 className="text-xl font-bold text-text-heading mb-1">Application Not Approved</h2>
          <p className="text-sm text-muted">Reference #{data.id}</p>
        </div>
        <StatusBadge status={data.status} />
      </div>

      <p className="text-body mb-4">
        Thank you for your interest in becoming a VESTRA® distributor. Unfortunately, your application did not
        meet our current partnership criteria at this time.
      </p>

      {data.admin_notes && (
        <div className="bg-surface-page rounded-xl border border-default p-4 mb-6">
          <p className="text-sm text-muted">
            <strong className="text-text-heading">Note:</strong> {data.admin_notes}
          </p>
        </div>
      )}

      <Link
        href="/contact"
        className="inline-flex items-center gap-2 px-6 py-3 bg-secondary-600 text-white font-semibold rounded-xl hover:opacity-90"
      >
        Contact Sales
        <ArrowRight className="w-4 h-4" />
      </Link>
    </div>
  );
}

function InfoRow({ label, value }: { label: string; value: React.ReactNode }) {
  return (
    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-1 py-3 border-b border-default last:border-0">
      <span className="text-sm text-muted">{label}</span>
      <span className="text-sm font-semibold text-text-heading text-right">{value}</span>
    </div>
  );
}

function QuickAction({
  href,
  icon: Icon,
  label,
}: {
  href: string;
  icon: React.ElementType;
  label: string;
}) {
  return (
    <Link
      href={href}
      className="flex items-center gap-3 p-4 rounded-xl border border-default bg-surface-page hover:border-secondary-200 hover:bg-secondary-50/40 transition-colors-base"
    >
      <div className="p-2 rounded-lg bg-secondary-50 text-secondary-600">
        <Icon className="w-5 h-5" />
      </div>
      <span className="font-semibold text-text-heading text-sm">{label}</span>
      <ArrowRight className="w-4 h-4 ml-auto text-muted" />
    </Link>
  );
}

function ApprovedDashboard({ application }: { application: DistributorRequest }) {
  const { data: dashboard, isLoading, error } = useDistributorDashboard();

  if (isLoading) {
    return (
      <div className="min-h-[40vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
      </div>
    );
  }

  if (error || !dashboard) {
    return (
      <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-8 text-center">
        <AlertCircle className="w-10 h-10 mx-auto mb-3 text-placeholder" />
        <h2 className="text-lg font-bold text-text-heading mb-2">Could not load distributor dashboard</h2>
        <p className="text-sm text-muted mb-6">
          Your application is approved. Open the full distributor portal or try again shortly.
        </p>
        <Link
          href="/distributor/dashboard"
          className="inline-flex items-center gap-2 px-6 py-3 bg-secondary-600 text-white font-semibold rounded-xl hover:opacity-90"
        >
          Go to Distributor Portal
          <ArrowRight className="w-4 h-4" />
        </Link>
      </div>
    );
  }

  return <ApprovedDashboardContent dashboard={dashboard} application={application} />;
}

function ApprovedDashboardContent({
  dashboard,
  application,
}: {
  dashboard: DistributorDashboard;
  application: DistributorRequest;
}) {
  const { distributor, stats, recent_orders, recent_quotes, recent_notifications } = dashboard;
  const credit = distributor.credit_account;
  const territory =
    [distributor.district, distributor.city, distributor.country].filter(Boolean).join(", ") || "—";
  const contactEmail = distributor.email;

  return (
    <div className="space-y-6">
      <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
        <div className="flex flex-col lg:flex-row lg:items-start justify-between gap-4 mb-6">
          <div>
            <h2 className="text-xl font-bold text-text-heading mb-1">Distributor Dashboard</h2>
            <p className="text-sm text-muted">
              {distributor.trading_name || distributor.company_name}
            </p>
          </div>
          <StatusBadge status={distributor.status} />
        </div>

        <div className="grid lg:grid-cols-2 gap-x-10">
          <InfoRow label="Distributor Name" value={distributor.company_name} />
          <InfoRow label="Company" value={distributor.trading_name || distributor.company_name} />
          <InfoRow label="Distributor ID" value={`#${distributor.id}`} />
          <InfoRow label="Date Approved" value={formatDate(distributor.approved_at || application.updated_at)} />
          <InfoRow label="Territory" value={territory} />
          <InfoRow label="Credit Status" value={credit?.status || "—"} />
          <InfoRow label="Credit Limit" value={formatMoney(credit?.limit ?? stats.credit_limit)} />
          <InfoRow
            label="Outstanding Balance"
            value={formatMoney(stats.outstanding_balance)}
          />
          <InfoRow label="Available Credit" value={formatMoney(credit?.available_credit ?? stats.available_credit)} />
          <InfoRow label="Application Ref" value={`#${application.id}`} />
        </div>
      </div>

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {[
          { label: "Credit Limit", value: formatMoney(stats.credit_limit), icon: CreditCard },
          { label: "Available Credit", value: formatMoney(stats.available_credit), icon: CreditCard },
          { label: "Quotes Submitted", value: String(stats.total_quotes), icon: FileSpreadsheet },
          { label: "Total Orders", value: String(stats.total_orders), icon: ShoppingCart },
        ].map((stat) => (
          <div key={stat.label} className="bg-surface-card rounded-[20px] border border-default shadow-sm p-5">
            <div className="flex items-start justify-between gap-3">
              <div className="min-w-0">
                <p className="text-sm text-muted">{stat.label}</p>
                <p className="text-xl font-extrabold text-text-heading mt-1 truncate">{stat.value}</p>
              </div>
              <div className="p-2.5 rounded-xl bg-secondary-50 text-secondary-600">
                <stat.icon className="w-5 h-5" />
              </div>
            </div>
          </div>
        ))}
      </div>

      <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
        <h3 className="text-lg font-bold text-text-heading mb-4">Quick Actions</h3>
        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <QuickAction href="/distributor/products" icon={Package} label="Browse Products" />
          <QuickAction href="/distributor/quotes/new" icon={FileSpreadsheet} label="Request Quote" />
          <QuickAction href="/distributor/statements" icon={CreditCard} label="View Credit" />
          <QuickAction href="/distributor/documents" icon={FolderOpen} label="Download Marketing Material" />
          {contactEmail ? (
            <a
              href={`mailto:${contactEmail}`}
              className="flex items-center gap-3 p-4 rounded-xl border border-default bg-surface-page hover:border-secondary-200 hover:bg-secondary-50/40 transition-colors-base"
            >
              <div className="p-2 rounded-lg bg-secondary-50 text-secondary-600">
                <Mail className="w-5 h-5" />
              </div>
              <span className="font-semibold text-text-heading text-sm">Contact Account Manager</span>
              <ArrowRight className="w-4 h-4 ml-auto text-muted" />
            </a>
          ) : (
            <QuickAction href="/contact" icon={Mail} label="Contact Account Manager" />
          )}
          <QuickAction href="/distributor/dashboard" icon={Building2} label="Full Distributor Portal" />
        </div>
      </div>

      <div className="grid lg:grid-cols-2 gap-6">
        <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-lg font-bold text-text-heading">Recent Activity</h3>
            <Bell className="w-5 h-5 text-muted" />
          </div>
          {recent_notifications.length === 0 && recent_quotes.length === 0 && recent_orders.length === 0 ? (
            <p className="text-sm text-muted py-4">No recent activity yet.</p>
          ) : (
            <div className="space-y-3">
              {recent_notifications.slice(0, 5).map((n) => (
                <div key={n.id} className="p-3 rounded-xl bg-surface-page border border-default">
                  <p className="text-sm font-semibold text-text-heading">{n.title}</p>
                  <p className="text-xs text-muted mt-0.5">{n.message}</p>
                  <p className="text-xs text-placeholder mt-1">{formatDate(n.created_at)}</p>
                </div>
              ))}
              {recent_quotes.slice(0, 3).map((q) => (
                <div key={`q-${q.id}`} className="p-3 rounded-xl bg-surface-page border border-default flex justify-between gap-3">
                  <div>
                    <p className="text-sm font-semibold text-text-heading">Quote Submitted</p>
                    <p className="text-xs text-muted">{q.reference_number}</p>
                  </div>
                  <span className="text-xs text-placeholder whitespace-nowrap">{formatDate(q.created_at)}</span>
                </div>
              ))}
              {recent_orders.slice(0, 3).map((o) => (
                <div key={`o-${o.id}`} className="p-3 rounded-xl bg-surface-page border border-default flex justify-between gap-3">
                  <div>
                    <p className="text-sm font-semibold text-text-heading">Order Update</p>
                    <p className="text-xs text-muted capitalize">{o.invoice_number} · {o.status}</p>
                  </div>
                  <span className="text-xs text-placeholder whitespace-nowrap">{formatDate(o.created_at)}</span>
                </div>
              ))}
            </div>
          )}
        </div>

        <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
          <h3 className="text-lg font-bold text-text-heading mb-4">Application History</h3>
          <Timeline steps={buildTimeline(application, "approved")} />
        </div>
      </div>
    </div>
  );
}

export function DistributorPageClient() {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();
  const { data, isLoading: statusLoading } = useDistributorApplicationStatus();

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  if (authLoading || statusLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
      </div>
    );
  }

  if (!isAuthenticated) return null;

  const isApproved = data?.status === "approved";

  let content;
  if (!data) {
    content = <NoApplicationState />;
  } else if (data.status === "pending") {
    content = <PendingState data={data} />;
  } else if (data.status === "approved") {
    content = <ApprovedDashboard application={data} />;
  } else {
    content = <RejectedState data={data} />;
  }

  return (
    <>
      <PageHero
        title={isApproved ? "Distributor" : "Distributor Application"}
        subtitle={
          isApproved
            ? "Your distributor status, credit, and recent activity"
            : "Track your distributor partnership application"
        }
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Distributor" }]}
      />

      <section className="py-8 lg:py-12 bg-surface-page">
        <Container>{content}</Container>
      </section>
    </>
  );
}
