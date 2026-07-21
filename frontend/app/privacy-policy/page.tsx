import { Metadata } from "next";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { createMetadata } from "@/lib/metadata";
import { JsonLd, breadcrumbSchema } from "@/lib/structured-data";

export const metadata: Metadata = createMetadata({
  title: "Privacy Policy",
  description: "Read VESTRA's privacy policy to understand how we collect, use, and protect your information.",
  pathname: "/privacy-policy",
});

export default function PrivacyPolicyPage() {
  return (
    <>
      <JsonLd
        data={breadcrumbSchema([
          { name: "Home", url: "https://vestra.com/" },
          { name: "Privacy Policy", url: "https://vestra.com/privacy-policy" },
        ])}
      />
      <main>
        <PageHero
          title="Privacy Policy"
          subtitle="Your privacy matters to us. This policy explains how we collect, use, and protect your information."
          breadcrumb={[{ label: "Privacy Policy" }]}
        />

        <section className="py-16 lg:py-24 bg-white">
        <Container className="max-w-4xl">
          <div className="prose prose-lg max-w-none text-[#475569]">
            <h2 className="text-2xl font-bold text-[#0a1628] mt-8 mb-4">1. Information We Collect</h2>
            <p>
              We collect information you provide directly to us, such as your name, email address,
              phone number, and business details when you fill out contact or distributor forms.
            </p>

            <h2 className="text-2xl font-bold text-[#0a1628] mt-8 mb-4">2. How We Use Your Information</h2>
            <p>
              We use your information to respond to inquiries, process distributor applications,
              improve our products and services, and communicate updates about VESTRA.
            </p>

            <h2 className="text-2xl font-bold text-[#0a1628] mt-8 mb-4">3. Information Sharing</h2>
            <p>
              We do not sell your personal information. We may share information with trusted
              service providers who assist us in operating our business.
            </p>

            <h2 className="text-2xl font-bold text-[#0a1628] mt-8 mb-4">4. Data Security</h2>
            <p>
              We implement reasonable security measures to protect your information from
              unauthorized access, disclosure, or destruction.
            </p>

            <h2 className="text-2xl font-bold text-[#0a1628] mt-8 mb-4">5. Your Rights</h2>
            <p>
              You have the right to access, update, or request deletion of your personal
              information. Contact us to exercise these rights.
            </p>

            <h2 className="text-2xl font-bold text-[#0a1628] mt-8 mb-4">6. Changes to This Policy</h2>
            <p>
              We may update this privacy policy from time to time. Changes will be posted on this
              page with an updated effective date.
            </p>

            <h2 className="text-2xl font-bold text-[#0a1628] mt-8 mb-4">7. Contact Us</h2>
            <p>
              If you have any questions about this privacy policy, please contact us at
              vestradetergent@gmail.com.
            </p>
          </div>
        </Container>
      </section>
    </main>
    </>
  );
}
