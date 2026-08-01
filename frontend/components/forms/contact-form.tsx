"use client";

import { useState, FormEvent, useRef } from "react";
import { CheckCircle, Loader2, Send, AlertCircle, Paperclip, X } from "lucide-react";
import { InputField, TextareaField, SelectField } from "@/components/common/form-field";
import { useContactMutation } from "@/hooks/use-contact";
import { cn } from "@/lib/utils";
import type { ContactEnquiryType, ContactFormData } from "@/types";

interface FormErrors {
  name?: string;
  company?: string;
  email?: string;
  phone?: string;
  subject?: string;
  enquiry_type?: string;
  message?: string;
  attachments?: string;
  _server?: string;
}

interface ContactFormProps {
  defaultSubject?: string;
  defaultEnquiryType?: ContactEnquiryType;
}

const enquiryOptions: { value: ContactEnquiryType; label: string }[] = [
  { value: "general", label: "General Enquiry" },
  { value: "sales", label: "Sales" },
  { value: "distributor", label: "Distributor" },
  { value: "quote", label: "Quote" },
  { value: "technical_support", label: "Technical Support" },
  { value: "other", label: "Other" },
];

function validateEmail(email: string): boolean {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function formatFileSize(bytes: number): string {
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

export function ContactForm({ defaultSubject, defaultEnquiryType = "general" }: ContactFormProps) {
  const [submitted, setSubmitted] = useState(false);
  const [errors, setErrors] = useState<FormErrors>({});
  const [files, setFiles] = useState<File[]>([]);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const mutation = useContactMutation();

  const validate = (data: ContactFormData, selectedFiles: File[]): FormErrors => {
    const next: FormErrors = {};

    if (!data.name || data.name.length < 2) next.name = "Name must be at least 2 characters.";
    if (!data.email || !validateEmail(data.email)) next.email = "Please enter a valid email address.";
    if (!data.subject || data.subject.length < 3) next.subject = "Subject must be at least 3 characters.";
    if (!data.enquiry_type) next.enquiry_type = "Please select an enquiry type.";
    if (!data.message || data.message.length < 10) next.message = "Message must be at least 10 characters.";

    const oversized = selectedFiles.filter((file) => file.size > 5 * 1024 * 1024);
    if (oversized.length > 0) {
      next.attachments = `Each file must be under 5 MB. (${oversized.map((f) => f.name).join(", ")})`;
    }

    return next;
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const selected = Array.from(e.target.files || []);
    setFiles((prev) => {
      const combined = [...prev, ...selected];
      return combined.slice(0, 5);
    });
    if (errors.attachments) {
      setErrors((prev) => ({ ...prev, attachments: undefined }));
    }
  };

  const removeFile = (index: number) => {
    setFiles((prev) => prev.filter((_, i) => i !== index));
  };

  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setSubmitted(false);

    const formData = new FormData(e.currentTarget);
    const data: ContactFormData = {
      name: formData.get("name")?.toString().trim() || "",
      company: formData.get("company")?.toString().trim() || undefined,
      email: formData.get("email")?.toString().trim() || "",
      phone: formData.get("phone")?.toString().trim() || undefined,
      subject: formData.get("subject")?.toString().trim() || "",
      enquiry_type: (formData.get("enquiry_type")?.toString() || "general") as ContactEnquiryType,
      message: formData.get("message")?.toString().trim() || "",
      attachments: files.length > 0 ? (files as unknown as FileList) : null,
    };

    const validationErrors = validate(data, files);
    if (Object.keys(validationErrors).length > 0) {
      setErrors(validationErrors);
      return;
    }

    setErrors({});

    mutation.mutate(data, {
      onSuccess: () => {
        setSubmitted(true);
        setFiles([]);
        e.currentTarget.reset();
      },
      onError: (error) => {
        if (error instanceof Error && "errors" in error) {
          const apiError = error as Error & { errors?: Record<string, string[]> };
          const serverErrors: FormErrors = {};
          if (apiError.errors) {
            Object.entries(apiError.errors).forEach(([key, messages]) => {
              if (messages && messages.length > 0) {
                (serverErrors as Record<string, string>)[key] = messages[0];
              }
            });
          }
          setErrors(serverErrors);
        } else {
          setErrors({ _server: error.message || "Something went wrong. Please try again." });
        }
      },
    });
  };

  if (submitted) {
    return (
      <div className="text-center py-12">
        <CheckCircle className="w-16 h-16 text-success-500 mx-auto mb-4" aria-hidden="true" />
        <h3 className="text-2xl font-bold text-primary-900 mb-2">Message Sent</h3>
        <p className="text-muted mb-6">
          Thank you for contacting VESTRA®. Our team will respond within 24–48 business hours.
        </p>
        <div className="flex flex-col sm:flex-row gap-3 justify-center">
          <a
            href="/request-quote"
            className="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full font-semibold text-white bg-gradient-to-br from-secondary-500 to-secondary-600 shadow-lg hover:-translate-y-0.5 transition-transform-base"
          >
            Request a Quote
          </a>
          <a
            href="/distributor"
            className="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full font-semibold text-primary-900 bg-surface-card border border-default hover:bg-surface-page transition-colors-base"
          >
            Become a Distributor
          </a>
        </div>
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-5" noValidate>
      {errors._server && (
        <div className="flex items-center gap-2 p-3 rounded-lg bg-danger-50 text-danger-600 text-sm">
          <AlertCircle className="w-4 h-4 flex-shrink-0" />
          {errors._server}
        </div>
      )}

      <div className="grid sm:grid-cols-2 gap-5">
        <InputField id="name" name="name" label="Your Name" placeholder="John Doe" error={errors.name} />
        <InputField
          id="company"
          name="company"
          label="Company (optional)"
          placeholder="Your company"
          error={errors.company}
        />
      </div>

      <div className="grid sm:grid-cols-2 gap-5">
        <InputField
          id="email"
          name="email"
          type="email"
          label="Your Email"
          placeholder="john@example.com"
          error={errors.email}
        />
        <InputField
          id="phone"
          name="phone"
          type="tel"
          label="Phone (optional)"
          placeholder="+256 707 128 442"
          error={errors.phone}
        />
      </div>

      <div className="grid sm:grid-cols-2 gap-5">
        <InputField
          id="subject"
          name="subject"
          label="Subject"
          placeholder="How can we help?"
          error={errors.subject}
          defaultValue={defaultSubject}
        />
        <SelectField
          id="enquiry_type"
          name="enquiry_type"
          label="Enquiry Type"
          options={enquiryOptions}
          error={errors.enquiry_type}
          defaultValue={defaultEnquiryType}
        />
      </div>

      <TextareaField
        id="message"
        name="message"
        label="Your Message"
        placeholder="Tell us more about your inquiry..."
        rows={5}
        error={errors.message}
      />

      {/* Attachments */}
      <div className="space-y-2">
        <label htmlFor="attachments" className="block text-sm font-semibold text-text-heading">
          Attachments (optional)
        </label>
        <input
          id="attachments"
          ref={fileInputRef}
          type="file"
          multiple
          accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
          onChange={handleFileChange}
          className="hidden"
        />
        <button
          type="button"
          onClick={() => fileInputRef.current?.click()}
          className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-default bg-surface-page text-primary-900 text-sm font-medium hover:bg-neutral-50 transition-colors-base"
        >
          <Paperclip className="w-4 h-4" aria-hidden="true" />
          Add files
        </button>
        {files.length > 0 && (
          <ul className="space-y-2 mt-2">
            {files.map((file, index) => (
              <li
                key={`${file.name}-${index}`}
                className="flex items-center justify-between gap-3 px-3 py-2 rounded-xl border border-default bg-surface-page text-sm"
              >
                <span className="truncate">
                  {file.name} <span className="text-muted">({formatFileSize(file.size)})</span>
                </span>
                <button
                  type="button"
                  onClick={() => removeFile(index)}
                  className="p-1 text-muted hover:text-danger-500 transition-colors-base"
                  aria-label={`Remove ${file.name}`}
                >
                  <X className="w-4 h-4" />
                </button>
              </li>
            ))}
          </ul>
        )}
        {errors.attachments && (
          <p id="attachments-error" className="text-sm text-danger-500" role="alert">
            {errors.attachments}
          </p>
        )}
        <p className="text-xs text-muted">Up to 5 files. PDF, JPG, PNG, DOC/DOCX. Max 5 MB each.</p>
      </div>

      <button
        type="submit"
        disabled={mutation.isPending}
        className={cn(
          "w-full inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-secondary-500 to-secondary-600 shadow-lg shadow-secondary-500/30 hover:-translate-y-1 transition-all-base",
          mutation.isPending && "opacity-70 cursor-not-allowed"
        )}
      >
        {mutation.isPending ? (
          <>
            <Loader2 className="w-4 h-4 animate-spin" />
            Sending...
          </>
        ) : (
          <>
            <Send className="w-4 h-4" />
            Send Message
          </>
        )}
      </button>
    </form>
  );
}
