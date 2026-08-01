"use client";

import { useState } from "react";
import { motion } from "framer-motion";
import { Mail, CheckCircle } from "lucide-react";
import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";

const interestOptions = [
  "Laundry Tips",
  "Commercial Cleaning",
  "Distributor Updates",
  "Product News",
  "Industry Insights",
];

export function NewsletterSection() {
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [interests, setInterests] = useState<string[]>([]);
  const [submitted, setSubmitted] = useState(false);

  const toggleInterest = (interest: string) => {
    setInterests((prev) =>
      prev.includes(interest) ? prev.filter((i) => i !== interest) : [...prev, interest]
    );
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!email) return;
    // Placeholder: wire up to a mailing platform when ready.
    setSubmitted(true);
  };

  return (
    <section className="py-20 lg:py-28 bg-surface-page" aria-labelledby="newsletter-heading">
      <Container>
        <motion.div
          initial={{ opacity: 0, y: 40 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, margin: "-100px" }}
          transition={{ duration: 0.7 }}
          className="max-w-3xl mx-auto"
        >
          <SectionHeader
            id="newsletter-heading"
            title="Stay Updated"
            subtitle="Subscribe for the latest articles, product insights, and commercial cleaning advice from VESTRA®."
          />

          {submitted ? (
            <div className="bg-white rounded-[24px] border border-default shadow-lg p-8 lg:p-12 text-center">
              <div className="w-16 h-16 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-5">
                <CheckCircle className="w-8 h-8 text-green-600" aria-hidden="true" />
              </div>
              <h3 className="text-xl font-bold text-text-heading mb-2">Thank You for Subscribing</h3>
              <p className="text-text-muted">
                You will receive our latest Knowledge Centre updates at {email}.
              </p>
            </div>
          ) : (
            <form
              onSubmit={handleSubmit}
              className="bg-white rounded-[24px] border border-default shadow-lg p-6 lg:p-10"
            >
              <div className="grid sm:grid-cols-2 gap-4 mb-5">
                <div>
                  <label htmlFor="newsletter-name" className="block text-sm font-medium text-text-heading mb-1.5">
                    Name
                  </label>
                  <input
                    id="newsletter-name"
                    type="text"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    placeholder="Your name"
                    className="w-full px-4 py-3 rounded-xl border border-default bg-surface-page text-text-heading placeholder:text-placeholder outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 transition-all-base"
                  />
                </div>
                <div>
                  <label htmlFor="newsletter-email" className="block text-sm font-medium text-text-heading mb-1.5">
                    Email <span className="text-danger-500">*</span>
                  </label>
                  <input
                    id="newsletter-email"
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="you@company.com"
                    required
                    className="w-full px-4 py-3 rounded-xl border border-default bg-surface-page text-text-heading placeholder:text-placeholder outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 transition-all-base"
                  />
                </div>
              </div>

              <fieldset className="mb-6">
                <legend className="block text-sm font-medium text-text-heading mb-3">Interests</legend>
                <div className="flex flex-wrap gap-2">
                  {interestOptions.map((interest) => {
                    const selected = interests.includes(interest);
                    return (
                      <button
                        key={interest}
                        type="button"
                        onClick={() => toggleInterest(interest)}
                        className={`px-4 py-2 rounded-full text-sm font-medium border transition-all-base ${
                          selected
                            ? "bg-primary-600 border-primary-600 text-white"
                            : "bg-surface-page border-default text-text-heading hover:border-primary-300"
                        }`}
                        aria-pressed={selected}
                      >
                        {interest}
                      </button>
                    );
                  })}
                </div>
              </fieldset>

              <button
                type="submit"
                className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-secondary-500 to-secondary-600 shadow-lg shadow-secondary-500/30 hover:-translate-y-0.5 transition-transform-base"
              >
                <Mail className="w-4 h-4" aria-hidden="true" />
                Subscribe
              </button>
            </form>
          )}
        </motion.div>
      </Container>
    </section>
  );
}
