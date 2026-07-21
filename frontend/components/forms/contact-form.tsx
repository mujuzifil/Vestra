"use client";

import { useState, FormEvent } from "react";
import { CheckCircle, Loader2, Send, AlertCircle } from "lucide-react";
import { InputField, TextareaField } from "@/components/common/form-field";
import { useContactMutation } from "@/hooks/use-contact";
import { cn } from "@/lib/utils";

interface FormErrors {
  name?: string;
  email?: string;
  subject?: string;
  message?: string;
  _server?: string;
}

export function ContactForm() {
  const [submitted, setSubmitted] = useState(false);
  const [errors, setErrors] = useState<FormErrors>({});

  const mutation = useContactMutation();

  const validate = (formData: FormData): FormErrors => {
    const next: FormErrors = {};
    const name = formData.get("name")?.toString().trim();
    const email = formData.get("email")?.toString().trim();
    const subject = formData.get("subject")?.toString().trim();
    const message = formData.get("message")?.toString().trim();

    if (!name || name.length < 2) next.name = "Name must be at least 2 characters.";
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      next.email = "Please enter a valid email address.";
    }
    if (!subject || subject.length < 3) next.subject = "Subject must be at least 3 characters.";
    if (!message || message.length < 10) next.message = "Message must be at least 10 characters.";

    return next;
  };

  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setSubmitted(false);
    const formData = new FormData(e.currentTarget);
    const validationErrors = validate(formData);

    if (Object.keys(validationErrors).length > 0) {
      setErrors(validationErrors);
      return;
    }

    setErrors({});

    const data = {
      name: formData.get("name")?.toString().trim() || "",
      email: formData.get("email")?.toString().trim() || "",
      subject: formData.get("subject")?.toString().trim() || "",
      message: formData.get("message")?.toString().trim() || "",
    };

    mutation.mutate(data, {
      onSuccess: () => {
        setSubmitted(true);
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
        <CheckCircle className="w-16 h-16 text-green-500 mx-auto mb-4" aria-hidden="true" />
        <h3 className="text-2xl font-bold text-[#0a1628] mb-2">Message Sent</h3>
        <p className="text-[#64748b]">
          Thank you for reaching out. We will respond as soon as possible.
        </p>
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-5" noValidate>
      {errors._server && (
        <div className="flex items-center gap-2 p-3 rounded-lg bg-red-50 text-red-600 text-sm">
          <AlertCircle className="w-4 h-4 flex-shrink-0" />
          {errors._server}
        </div>
      )}
      <div className="grid sm:grid-cols-2 gap-5">
        <InputField id="name" name="name" label="Your Name" placeholder="John Doe" error={errors.name} />
        <InputField
          id="email"
          name="email"
          type="email"
          label="Your Email"
          placeholder="john@example.com"
          error={errors.email}
        />
      </div>
      <InputField
        id="subject"
        name="subject"
        label="Subject"
        placeholder="How can we help?"
        error={errors.subject}
      />
      <TextareaField
        id="message"
        name="message"
        label="Your Message"
        placeholder="Tell us more about your inquiry..."
        rows={5}
        error={errors.message}
      />
      <button
        type="submit"
        disabled={mutation.isPending}
        className={cn(
          "w-full inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-1 transition-all",
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
