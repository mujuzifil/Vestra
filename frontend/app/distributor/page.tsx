"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import { Loader2, Clock, CheckCircle2, XCircle, ArrowRight } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { SectionHeader } from "@/components/common/section-header";
import { DistributorForm } from "@/components/forms/distributor-form";
import { AnimatedSection } from "@/components/common/animated-section";
import { ValueCard } from "@/components/common/value-card";
import { FAQAccordion } from "@/components/common/faq-accordion";
import { CTASection } from "@/components/common/cta-section";
import { useAuth } from "@/lib/auth-context";
import { useDistributorApplicationStatus } from "@/hooks/use-distributor-application-status";
import Link from "next/link";

const distributorBenefits = [
  { icon: "Award", title: "Exclusive Territory", description: "Gain protected distribution rights in your region." },
  { icon: "Truck", title: "Reliable Supply", description: "Consistent product availability and timely deliveries." },
  { icon: "BadgeCheck", title: "Marketing Support", description: "Access branded materials and promotional resources." },
  { icon: "Sparkles", title: "Training", description: "Receive product knowledge and sales training from our team." },
];

const distributorFaqs = [
  {
    question: "What is the minimum order quantity?",
    answer: "Minimum order quantities vary by product. Our team will share the full price list and MOQ after reviewing your application.",
  },
  {
    question: "Do I need a registered business to apply?",
    answer: "Yes, we prefer working with registered businesses that have experience in distribution or retail.",
  },
  {
    question: "How long does the approval process take?",
    answer: "Applications are typically reviewed within 5-7 business days.",
  },
  {
    question: "What support do distributors receive?",
    answer: "Distributors receive marketing materials, training, territory protection, and dedicated account support.",
  },
];

function ApplicationStatusCard({ status }: { status: string }) {
  if (status === "approved") {
    return (
      <div className="p-6 lg:p-10 rounded-[24px] bg-white border border-[#e2e8f0] shadow-lg">
        <div className="flex items-center gap-3 mb-4">
          <div className="p-2 rounded-full bg-green-100">
            <CheckCircle2 className="w-6 h-6 text-green-600" />
          </div>
          <h2 className="text-xl lg:text-2xl font-bold text-[#0a1628]">Application Approved</h2>
        </div>
        <p className="text-[#64748b] mb-6">
          Congratulations! Your distributor application has been approved. You can now access the distributor portal.
        </p>
        <Link
          href="/distributor/dashboard"
          className="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors"
        >
          Go to Dashboard
          <ArrowRight className="w-4 h-4" />
        </Link>
      </div>
    );
  }

  if (status === "rejected") {
    return (
      <div className="p-6 lg:p-10 rounded-[24px] bg-white border border-[#e2e8f0] shadow-lg">
        <div className="flex items-center gap-3 mb-4">
          <div className="p-2 rounded-full bg-red-100">
            <XCircle className="w-6 h-6 text-red-600" />
          </div>
          <h2 className="text-xl lg:text-2xl font-bold text-[#0a1628]">Application Not Approved</h2>
        </div>
        <p className="text-[#64748b] mb-6">
          Thank you for your interest. Unfortunately, your application was not approved at this time. Please contact our partnership team for more information.
        </p>
        <Link
          href="/contact"
          className="inline-flex items-center gap-2 px-6 py-3 bg-[#0a1628] text-white font-semibold rounded-xl hover:bg-[#1a2638] transition-colors"
        >
          Contact Us
          <ArrowRight className="w-4 h-4" />
        </Link>
      </div>
    );
  }

  return (
    <div className="p-6 lg:p-10 rounded-[24px] bg-white border border-[#e2e8f0] shadow-lg">
      <div className="flex items-center gap-3 mb-4">
        <div className="p-2 rounded-full bg-amber-100">
          <Clock className="w-6 h-6 text-amber-600" />
        </div>
        <h2 className="text-xl lg:text-2xl font-bold text-[#0a1628]">Application Pending</h2>
      </div>
      <p className="text-[#64748b] mb-6">
        Your distributor application is currently under review. Our team will get back to you within 5-7 business days.
      </p>
      <Link
        href="/account"
        className="inline-flex items-center gap-2 text-green-600 font-semibold hover:text-green-700"
      >
        Go to My Account
        <ArrowRight className="w-4 h-4" />
      </Link>
    </div>
  );
}

export default function DistributorPage() {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading, user } = useAuth();
  const { data: application, isLoading: statusLoading } = useDistributorApplicationStatus(isAuthenticated);

  useEffect(() => {
    if (!authLoading && isAuthenticated && user?.roles?.includes("distributor")) {
      router.push("/distributor/dashboard");
    }
  }, [authLoading, isAuthenticated, user, router]);

  const isLoading = authLoading || statusLoading;

  return (
    <main>
      <PageHero
        title="Become a Distributor"
        subtitle="Join the VESTRA network and bring professional fabric care solutions to your market."
        breadcrumb={[{ label: "Distributor" }]}
      />

      {/* Benefits */}
      <section className="py-20 lg:py-28 bg-white" aria-labelledby="benefits-heading">
        <Container>
          <SectionHeader
            id="benefits-heading"
            title="Why Partner with VESTRA"
            subtitle="We support our distributors with the tools, training, and products they need to succeed."
          />
          <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {distributorBenefits.map((benefit, index) => (
              <ValueCard key={benefit.title} icon={benefit.icon} title={benefit.title} description={benefit.description} index={index} />
            ))}
          </div>
        </Container>
      </section>

      {/* Opportunities & Form */}
      <section className="py-20 lg:py-28 bg-[#f8fafc]" aria-labelledby="application-heading">
        <Container>
          <div className="grid lg:grid-cols-2 gap-12 items-center">
            <AnimatedSection direction="left">
              <SectionHeader
                id="application-heading"
                title="Business Opportunities"
                subtitle="Tailored partnership models for every scale of operation."
                centered={false}
              />
              <ul className="space-y-4">
                {[
                  "Retail distribution in supermarkets and shops",
                  "Supply to hotels, hospitals, and commercial laundries",
                  "Regional wholesale partnerships",
                  "Bulk corporate and institutional contracts",
                ].map((item, index) => (
                  <li key={item} className="flex items-start gap-4 text-[#475569] text-base lg:text-lg">
                    <span className="w-7 h-7 rounded-full bg-green-500 text-white flex items-center justify-center text-sm font-bold flex-shrink-0">
                      {index + 1}
                    </span>
                    {item}
                  </li>
                ))}
              </ul>
            </AnimatedSection>

            <AnimatedSection direction="right">
              {isLoading ? (
                <div className="p-6 lg:p-10 rounded-[24px] bg-white border border-[#e2e8f0] shadow-lg flex items-center justify-center min-h-[300px]">
                  <Loader2 className="w-8 h-8 animate-spin text-green-500" />
                </div>
              ) : isAuthenticated && application ? (
                <ApplicationStatusCard status={application.status} />
              ) : (
                <div className="p-6 lg:p-10 rounded-[24px] bg-white border border-[#e2e8f0] shadow-lg">
                  <h2 className="text-xl lg:text-2xl font-bold text-[#0a1628] mb-2">Distributor Application</h2>
                  <p className="text-[#64748b] mb-6">Complete the form below to start your partnership journey.</p>
                  <DistributorForm />
                </div>
              )}
            </AnimatedSection>
          </div>
        </Container>
      </section>

      {/* FAQ */}
      <section className="py-20 lg:py-28 bg-white" aria-labelledby="distributor-faq-heading">
        <Container>
          <div className="grid lg:grid-cols-[0.8fr_1.2fr] gap-12 lg:gap-16">
            <div>
              <SectionHeader
                id="distributor-faq-heading"
                title="Distributor FAQ"
                subtitle="Common questions about partnering with VESTRA."
                centered={false}
              />
            </div>
            <FAQAccordion items={distributorFaqs} />
          </div>
        </Container>
      </section>

      <CTASection
        title="Ready to grow with us?"
        description="Reach out directly and our partnership team will guide you through the next steps."
        buttonText="Contact Us"
        buttonHref="/contact"
      />
    </main>
  );
}
