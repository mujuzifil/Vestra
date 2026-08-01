"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import { Loader2, Clock, CheckCircle2, XCircle, ArrowRight } from "lucide-react";
import Link from "next/link";
import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { DistributorForm } from "@/components/forms/distributor-form";
import { AnimatedSection } from "@/components/common/animated-section";
import { FAQAccordion } from "@/components/common/faq-accordion";
import { CTASection } from "@/components/common/cta-section";
import { useAuth } from "@/lib/auth-context";
import { useDistributorApplicationStatus } from "@/hooks/use-distributor-application-status";
import { JsonLd, breadcrumbSchema } from "@/lib/structured-data";
import { DistributorHero } from "./_components/distributor-hero";
import { WhyPartnerSection } from "./_components/why-partner-section";
import { WhoCanApplySection } from "./_components/who-can-apply-section";
import { DistributorBenefitsSection } from "./_components/distributor-benefits-section";
import { ApplicationProcessSection } from "./_components/application-process-section";
import { DistributorStatsSection } from "./_components/distributor-stats-section";

const distributorFaqs = [
  {
    question: "What is the minimum order quantity?",
    answer:
      "Minimum order quantities vary by product. Our team will share the full price list and MOQ after reviewing your application.",
  },
  {
    question: "Do I need a registered business to apply?",
    answer:
      "Yes, we prefer working with registered businesses that have experience in distribution or retail.",
  },
  {
    question: "How long does the approval process take?",
    answer: "Applications are typically reviewed within 5–7 business days.",
  },
  {
    question: "What support do distributors receive?",
    answer:
      "Distributors receive marketing materials, training, territory protection, and dedicated account support.",
  },
  {
    question: "Which regions are available?",
    answer:
      "Territory availability depends on existing coverage. Submit your application and our team will discuss open regions with you.",
  },
  {
    question: "What documents should I upload?",
    answer:
      "Business registration certificates, trading licences, and company profiles help us verify your application faster.",
  },
];

function ApplicationStatusCard({ status }: { status: string }) {
  if (status === "approved") {
    return (
      <div className="p-6 lg:p-10 rounded-[24px] bg-white border border-neutral-200 shadow-lg">
        <div className="flex items-center gap-3 mb-4">
          <div className="p-2 rounded-full bg-green-100">
            <CheckCircle2 className="w-6 h-6 text-green-600" />
          </div>
          <h2 className="text-xl lg:text-2xl font-bold text-primary-900">Application Approved</h2>
        </div>
        <p className="text-muted mb-6">
          Congratulations! Your distributor application has been approved. You can now access the distributor portal.
        </p>
        <Link
          href="/distributor/dashboard"
          className="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors-base"
        >
          Go to Dashboard
          <ArrowRight className="w-4 h-4" />
        </Link>
      </div>
    );
  }

  if (status === "rejected") {
    return (
      <div className="p-6 lg:p-10 rounded-[24px] bg-white border border-neutral-200 shadow-lg">
        <div className="flex items-center gap-3 mb-4">
          <div className="p-2 rounded-full bg-red-100">
            <XCircle className="w-6 h-6 text-red-600" />
          </div>
          <h2 className="text-xl lg:text-2xl font-bold text-primary-900">Application Not Approved</h2>
        </div>
        <p className="text-muted mb-6">
          Thank you for your interest. Unfortunately, your application was not approved at this time. Please contact our partnership team for more information.
        </p>
        <Link
          href="/contact"
          className="inline-flex items-center gap-2 px-6 py-3 bg-primary-900 text-white font-semibold rounded-xl hover:bg-primary-900 transition-colors-base"
        >
          Contact Us
          <ArrowRight className="w-4 h-4" />
        </Link>
      </div>
    );
  }

  return (
    <div className="p-6 lg:p-10 rounded-[24px] bg-white border border-neutral-200 shadow-lg">
      <div className="flex items-center gap-3 mb-4">
        <div className="p-2 rounded-full bg-amber-100">
          <Clock className="w-6 h-6 text-amber-600" />
        </div>
        <h2 className="text-xl lg:text-2xl font-bold text-primary-900">Application Pending</h2>
      </div>
      <p className="text-muted mb-6">
        Your distributor application is currently under review. Our team will get back to you within 5–7 business days.
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
    <>
      <JsonLd
        data={breadcrumbSchema([
          { name: "Home", url: "https://vestradetergents.com/" },
          { name: "Become a Distributor", url: "https://vestradetergents.com/distributor" },
        ])}
      />
      <main>
        <DistributorHero />
        <WhyPartnerSection />
        <WhoCanApplySection />
        <DistributorBenefitsSection />
        <ApplicationProcessSection />

        {/* Application Form */}
        <section
          id="application-form"
          className="py-20 lg:py-28 bg-white"
          aria-labelledby="application-heading"
        >
          <Container>
            <div className="grid lg:grid-cols-2 gap-12 lg:gap-16 items-start">
              <AnimatedSection direction="left">
                <SectionHeader
                  id="application-heading"
                  title="Apply to Become a Distributor"
                  subtitle="Complete the form below. Our partnership team will review your application and contact you within 5–7 business days."
                  centered={false}
                />
                <ul className="space-y-4 mt-8">
                  {[
                    "Submit your business profile and distribution capacity.",
                    "Upload supporting documents for faster verification.",
                    "Receive confirmation with a unique reference number.",
                    "Work with our team to finalise territory and terms.",
                  ].map((item, index) => (
                    <li key={item} className="flex items-start gap-4 text-body text-base lg:text-lg">
                      <span className="w-7 h-7 rounded-full bg-secondary-500 text-white flex items-center justify-center text-sm font-bold flex-shrink-0">
                        {index + 1}
                      </span>
                      {item}
                    </li>
                  ))}
                </ul>
              </AnimatedSection>

              <AnimatedSection direction="right">
                {isLoading ? (
                  <div className="p-6 lg:p-10 rounded-[24px] bg-white border border-neutral-200 shadow-lg flex items-center justify-center min-h-[300px]">
                    <Loader2 className="w-8 h-8 animate-spin text-green-500" />
                  </div>
                ) : isAuthenticated && application ? (
                  <ApplicationStatusCard status={application.status} />
                ) : (
                  <div className="p-6 lg:p-10 rounded-[24px] bg-white border border-neutral-200 shadow-lg">
                    <h2 className="text-xl lg:text-2xl font-bold text-primary-900 mb-2">
                      Distributor Application
                    </h2>
                    <p className="text-muted mb-6">
                      All fields marked by the form are required unless indicated optional.
                    </p>
                    <DistributorForm />
                  </div>
                )}
              </AnimatedSection>
            </div>
          </Container>
        </section>

        <DistributorStatsSection />

        {/* FAQ */}
        <section className="py-20 lg:py-28 bg-white" aria-labelledby="distributor-faq-heading">
          <Container>
            <div className="grid lg:grid-cols-[0.8fr_1.2fr] gap-12 lg:gap-16">
              <div>
                <SectionHeader
                  id="distributor-faq-heading"
                  title="Distributor FAQ"
                  subtitle="Common questions about partnering with VESTRA®."
                  centered={false}
                />
              </div>
              <FAQAccordion items={distributorFaqs} />
            </div>
          </Container>
        </section>

        <CTASection
          title="Ready to grow with VESTRA®?"
          description="Apply today or speak with our partnership team about distribution opportunities in your region."
          buttonText="Apply Now"
          buttonHref="#application-form"
          secondaryButton={{ text: "Contact Sales", href: "/contact" }}
        />
      </main>
    </>
  );
}
