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
} from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useDistributorApplicationStatus } from "@/hooks/use-distributor-application-status";
import type { DistributorRequest } from "@/types";

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

function StatusBadge({ status }: { status: string }) {
  const styles: Record<string, string> = {
    pending: "bg-warning-100 text-warning-600",
    approved: "bg-secondary-100 text-secondary-600",
    rejected: "bg-danger-100 text-danger-600",
  };
  return (
    <span
      className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold capitalize ${
        styles[status] || "bg-neutral-100 text-text-body"
      }`}
    >
      {status === "pending" && <Clock className="w-3.5 h-3.5" />}
      {status === "approved" && <CheckCircle2 className="w-3.5 h-3.5" />}
      {status === "rejected" && <XCircle className="w-3.5 h-3.5" />}
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
  const steps: TimelineStep[] = [
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
  return steps;
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
          <div
            key={benefit.title}
            className="bg-surface-page rounded-xl border border-default p-4"
          >
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

function ApprovedState({ data }: { data: DistributorRequest }) {
  const steps = buildTimeline(data, "approved");
  return (
    <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
          <h2 className="text-xl font-bold text-text-heading mb-1">Distributor Application Approved</h2>
          <p className="text-sm text-muted">Reference #{data.id}</p>
        </div>
        <StatusBadge status={data.status} />
      </div>

      <p className="text-body mb-6">
        Congratulations, your application was approved on{" "}
        <strong className="text-text-heading">{formatDate(data.updated_at)}</strong>. You now have access to
        distributor resources, training materials, and preferential pricing.
      </p>

      <div className="bg-surface-page rounded-xl border border-default p-5 mb-6">
        <h3 className="font-semibold text-text-heading mb-4">Application Progress</h3>
        <Timeline steps={steps} />
      </div>

      <Link
        href="/distributor/dashboard"
        className="inline-flex items-center gap-2 px-6 py-3 bg-secondary-600 text-white font-semibold rounded-xl hover:opacity-90"
      >
        Go to Distributor Dashboard
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

  let content;
  if (!data) {
    content = <NoApplicationState />;
  } else if (data.status === "pending") {
    content = <PendingState data={data} />;
  } else if (data.status === "approved") {
    content = <ApprovedState data={data} />;
  } else {
    content = <RejectedState data={data} />;
  }

  return (
    <>
      <PageHero
        title="Distributor Application"
        subtitle="Track your distributor partnership application"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Distributor" }]}
      />

      <section className="py-12 lg:py-20 bg-surface-page">
        <Container>{content}</Container>
      </section>
    </>
  );
}
