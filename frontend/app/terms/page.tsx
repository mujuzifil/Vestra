import { Metadata } from "next";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { createMetadata } from "@/lib/metadata";
import { JsonLd, breadcrumbSchema } from "@/lib/structured-data";

export const metadata: Metadata = createMetadata({
  title: "Terms & Conditions",
  description: "Review the terms and conditions for using the VESTRA website and services.",
  pathname: "/terms",
});

export default function TermsPage() {
  return (
    <>
      <JsonLd
        data={breadcrumbSchema([
          { name: "Home", url: "https://vestra.com/" },
          { name: "Terms & Conditions", url: "https://vestra.com/terms" },
        ])}
      />
      <main>
        <PageHero
          title="Terms & Conditions"
          subtitle="Please read these terms carefully before using our website or services."
          breadcrumb={[{ label: "Terms & Conditions" }]}
        />

        <section className="py-16 lg:py-24 bg-white">
        <Container className="max-w-4xl">
          <div className="prose prose-lg max-w-none text-[#475569]">
            <h2 className="text-2xl font-bold text-[#0a1628] mt-8 mb-4">1. Acceptance of Terms</h2>
            <p>
              By accessing and using the VESTRA website, you accept and agree to be bound by these
              terms and conditions.
            </p>

            <h2 className="text-2xl font-bold text-[#0a1628] mt-8 mb-4">2. Use of Website</h2>
            <p>
              You agree to use this website for lawful purposes only and in a manner that does not
              infringe the rights of others or restrict their use of the website.
            </p>

            <h2 className="text-2xl font-bold text-[#0a1628] mt-8 mb-4">3. Products and Pricing</h2>
            <p>
              Product descriptions and prices are subject to change without notice. We reserve the
              right to modify or discontinue products at any time.
            </p>

            <h2 className="text-2xl font-bold text-[#0a1628] mt-8 mb-4">4. Distributor Applications</h2>
            <p>
              Submitting a distributor application does not guarantee approval. VESTRA reserves the
              right to approve or decline applications at its sole discretion.
            </p>

            <h2 className="text-2xl font-bold text-[#0a1628] mt-8 mb-4">5. Intellectual Property</h2>
            <p>
              All content on this website, including logos, text, images, and designs, is the
              property of VESTRA and protected by applicable intellectual property laws.
            </p>

            <h2 className="text-2xl font-bold text-[#0a1628] mt-8 mb-4">6. Limitation of Liability</h2>
            <p>
              VESTRA shall not be liable for any indirect, incidental, or consequential damages
              arising from the use of our website or products.
            </p>

            <h2 className="text-2xl font-bold text-[#0a1628] mt-8 mb-4">7. Governing Law</h2>
            <p>
              These terms are governed by the laws of Uganda. Any disputes shall be resolved in the
              courts of Uganda.
            </p>

            <h2 className="text-2xl font-bold text-[#0a1628] mt-8 mb-4">8. Contact</h2>
            <p>
              For questions about these terms, contact us at vestradetergent@gmail.com.
            </p>
          </div>
        </Container>
      </section>
    </main>
    </>
  );
}
