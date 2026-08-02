"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import {
  Phone,
  MessageCircle,
  Mail,
  Clock,
  Loader2,
  Headphones,
  ArrowRight,
  Send,
  Plus,
  X,
  Paperclip,
  ChevronDown,
  ChevronUp,
} from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useSupportTickets, useCreateSupportTicket } from "@/hooks/use-support-tickets";
import { useReplyToSupportTicket } from "@/hooks/use-support-ticket";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import type { SupportTicket } from "@/types";

const contactCards = [
  {
    icon: Phone,
    title: "Phone",
    value: "+256 707 128 442",
    href: "tel:+256707128442",
    label: "Call now",
  },
  {
    icon: MessageCircle,
    title: "WhatsApp",
    value: "+256 707 128 442",
    href: "https://wa.me/256707128442",
    label: "Message",
  },
  {
    icon: Mail,
    title: "Email",
    value: "info@vestradetergents.com",
    href: "mailto:info@vestradetergents.com",
    label: "Send email",
  },
  {
    icon: Clock,
    title: "Business Hours",
    value: "Mon – Fri: 8:00 AM – 6:00 PM",
    href: null,
    label: null,
  },
];

function statusVariant(status: string): "default" | "secondary" | "outline" | "danger" {
  switch (status) {
    case "open":
    case "in_progress":
      return "secondary";
    case "resolved":
    case "closed":
      return "default";
    default:
      return "outline";
  }
}

function TicketCard({ ticket }: { ticket: SupportTicket }) {
  const [expanded, setExpanded] = useState(false);
  const [replyMessage, setReplyMessage] = useState("");
  const [replyAttachments, setReplyAttachments] = useState<FileList | null>(null);
  const replyMutation = useReplyToSupportTicket(ticket.id);

  const handleReply = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!replyMessage.trim()) return;
    await replyMutation.mutateAsync({ message: replyMessage, attachments: replyAttachments });
    setReplyMessage("");
    setReplyAttachments(null);
  };

  return (
    <div className="p-4 rounded-xl border border-default bg-surface-page">
      <button
        type="button"
        onClick={() => setExpanded((v) => !v)}
        className="w-full flex items-start justify-between gap-4 text-left"
      >
        <div className="min-w-0">
          <div className="flex items-center gap-2 flex-wrap mb-1">
            <span className="font-semibold text-text-heading">{ticket.subject}</span>
            <Badge variant={statusVariant(ticket.status)}>{ticket.status.replace("_", " ")}</Badge>
          </div>
          <p className="text-sm text-muted">
            {ticket.reference_number} • {ticket.enquiry_type.replace("_", " ")} •{" "}
            {new Date(ticket.created_at).toLocaleDateString()}
          </p>
        </div>
        {expanded ? <ChevronUp className="w-5 h-5 text-muted" /> : <ChevronDown className="w-5 h-5 text-muted" />}
      </button>

      {expanded && (
        <div className="mt-4 pt-4 border-t border-default space-y-4">
          <div>
            <p className="text-sm text-text-heading whitespace-pre-line">{ticket.message}</p>
            {ticket.attachments && ticket.attachments.length > 0 && (
              <div className="flex flex-wrap gap-2 mt-3">
                {ticket.attachments.map((url, index) => (
                  <a
                    key={index}
                    href={url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-secondary-700 bg-secondary-50 rounded-lg hover:bg-secondary-100"
                  >
                    <Paperclip className="w-3.5 h-3.5" />
                    Attachment {index + 1}
                  </a>
                ))}
              </div>
            )}
          </div>

          {ticket.replies.length > 0 && (
            <div className="space-y-3">
              {ticket.replies.map((reply) => (
                <div key={reply.id} className="p-3 rounded-lg bg-surface-card border border-default">
                  <div className="flex items-center justify-between gap-2 mb-1">
                    <span className="text-sm font-semibold text-text-heading">
                      {reply.author?.name ?? "Unknown"}
                    </span>
                    <span className="text-xs text-muted capitalize">{reply.author?.type ?? "customer"}</span>
                  </div>
                  <p className="text-sm text-muted whitespace-pre-line">{reply.message}</p>
                  {reply.attachments && reply.attachments.length > 0 && (
                    <div className="flex flex-wrap gap-2 mt-2">
                      {reply.attachments.map((url, index) => (
                        <a
                          key={index}
                          href={url}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-secondary-700 bg-secondary-50 rounded-lg hover:bg-secondary-100"
                        >
                          <Paperclip className="w-3.5 h-3.5" />
                          Attachment {index + 1}
                        </a>
                      ))}
                    </div>
                  )}
                </div>
              ))}
            </div>
          )}

          {ticket.status !== "closed" && (
            <form onSubmit={handleReply} className="space-y-3">
              <textarea
                value={replyMessage}
                onChange={(e) => setReplyMessage(e.target.value)}
                placeholder="Write a reply..."
                rows={3}
                className="w-full rounded-xl border border-default bg-surface-card px-4 py-3 text-sm text-text-heading placeholder:text-placeholder focus:outline-none focus:ring-2 focus:ring-secondary-500"
              />
              <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <label className="inline-flex items-center gap-2 text-sm text-muted cursor-pointer">
                  <Paperclip className="w-4 h-4" />
                  <span>Attach files</span>
                  <input
                    type="file"
                    multiple
                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                    onChange={(e) => setReplyAttachments(e.target.files)}
                    className="hidden"
                  />
                </label>
                <Button
                  type="submit"
                  disabled={replyMutation.isPending || !replyMessage.trim()}
                  className="inline-flex items-center gap-2"
                >
                  {replyMutation.isPending ? (
                    <Loader2 className="w-4 h-4 animate-spin" />
                  ) : (
                    <Send className="w-4 h-4" />
                  )}
                  Send Reply
                </Button>
              </div>
            </form>
          )}
        </div>
      )}
    </div>
  );
}

export function SupportPageClient() {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();
  const { data, isLoading: ticketsLoading } = useSupportTickets(1);
  const createTicket = useCreateSupportTicket();
  const tickets = data?.data ?? [];

  const [showForm, setShowForm] = useState(false);
  const [subject, setSubject] = useState("");
  const [enquiryType, setEnquiryType] = useState<"general" | "sales" | "distributor" | "quote" | "technical_support" | "other">("general");
  const [message, setMessage] = useState("");
  const [priority, setPriority] = useState<"low" | "medium" | "high" | "urgent">("medium");
  const [attachments, setAttachments] = useState<FileList | null>(null);

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    await createTicket.mutateAsync({
      subject,
      message,
      enquiry_type: enquiryType,
      priority,
      attachments,
    });
    setShowForm(false);
    setSubject("");
    setMessage("");
    setEnquiryType("general");
    setPriority("medium");
    setAttachments(null);
  };

  if (authLoading || ticketsLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
      </div>
    );
  }

  if (!isAuthenticated) return null;

  return (
    <>
      <PageHero
        title="Support Centre"
        subtitle="We are here to help with your enquiries"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Support" }]}
      />

      <section className="py-12 lg:py-20 bg-surface-page">
        <Container>
          <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            {contactCards.map((card) => (
              <div
                key={card.title}
                className="bg-surface-card rounded-[20px] border border-default shadow-sm p-5"
              >
                <div className="w-10 h-10 rounded-xl bg-secondary-50 flex items-center justify-center mb-4">
                  <card.icon className="w-5 h-5 text-secondary-600" />
                </div>
                <p className="text-sm text-muted mb-1">{card.title}</p>
                <p className="font-semibold text-text-heading mb-3">{card.value}</p>
                {card.href && card.label && (
                  <a
                    href={card.href}
                    target={card.href.startsWith("http") ? "_blank" : undefined}
                    rel={card.href.startsWith("http") ? "noopener noreferrer" : undefined}
                    className="inline-flex items-center gap-1.5 text-sm font-semibold text-secondary-600 hover:text-secondary-600"
                  >
                    {card.label}
                    <ArrowRight className="w-3.5 h-3.5" />
                  </a>
                )}
              </div>
            ))}
          </div>

          <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-lg font-bold text-text-heading">Your Support Enquiries</h2>
              <Button
                onClick={() => setShowForm((v) => !v)}
                variant="outline"
                className="inline-flex items-center gap-2"
              >
                {showForm ? <X className="w-4 h-4" /> : <Plus className="w-4 h-4" />}
                {showForm ? "Cancel" : "New Enquiry"}
              </Button>
            </div>

            {showForm && (
              <form onSubmit={handleSubmit} className="mb-8 p-4 rounded-xl bg-surface-page border border-default space-y-4">
                <div className="grid sm:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-text-heading mb-1">Subject</label>
                    <input
                      type="text"
                      required
                      value={subject}
                      onChange={(e) => setSubject(e.target.value)}
                      placeholder="How can we help?"
                      className="w-full rounded-xl border border-default bg-surface-card px-4 py-2.5 text-sm text-text-heading placeholder:text-placeholder focus:outline-none focus:ring-2 focus:ring-secondary-500"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-text-heading mb-1">Enquiry Type</label>
                    <select
                      value={enquiryType}
                      onChange={(e) => setEnquiryType(e.target.value as typeof enquiryType)}
                      className="w-full rounded-xl border border-default bg-surface-card px-4 py-2.5 text-sm text-text-heading focus:outline-none focus:ring-2 focus:ring-secondary-500"
                    >
                      <option value="general">General</option>
                      <option value="sales">Sales</option>
                      <option value="quote">Quote</option>
                      <option value="distributor">Distributor</option>
                      <option value="technical_support">Technical Support</option>
                      <option value="other">Other</option>
                    </select>
                  </div>
                </div>
                <div>
                  <label className="block text-sm font-medium text-text-heading mb-1">Message</label>
                  <textarea
                    required
                    value={message}
                    onChange={(e) => setMessage(e.target.value)}
                    rows={4}
                    placeholder="Describe your enquiry in detail..."
                    className="w-full rounded-xl border border-default bg-surface-card px-4 py-3 text-sm text-text-heading placeholder:text-placeholder focus:outline-none focus:ring-2 focus:ring-secondary-500"
                  />
                </div>
                <div className="grid sm:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-text-heading mb-1">Priority</label>
                    <select
                      value={priority}
                      onChange={(e) => setPriority(e.target.value as typeof priority)}
                      className="w-full rounded-xl border border-default bg-surface-card px-4 py-2.5 text-sm text-text-heading focus:outline-none focus:ring-2 focus:ring-secondary-500"
                    >
                      <option value="low">Low</option>
                      <option value="medium">Medium</option>
                      <option value="high">High</option>
                      <option value="urgent">Urgent</option>
                    </select>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-text-heading mb-1">Attachments</label>
                    <label className="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-default bg-surface-card text-sm text-muted cursor-pointer hover:bg-surface-page">
                      <Paperclip className="w-4 h-4" />
                      <span>{attachments && attachments.length > 0 ? `${attachments.length} file(s)` : "Choose files"}</span>
                      <input
                        type="file"
                        multiple
                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                        onChange={(e) => setAttachments(e.target.files)}
                        className="hidden"
                      />
                    </label>
                  </div>
                </div>
                <div className="flex justify-end">
                  <Button
                    type="submit"
                    disabled={createTicket.isPending || !subject.trim() || !message.trim()}
                    className="inline-flex items-center gap-2"
                  >
                    {createTicket.isPending ? (
                      <Loader2 className="w-4 h-4 animate-spin" />
                    ) : (
                      <Send className="w-4 h-4" />
                    )}
                    Submit Enquiry
                  </Button>
                </div>
              </form>
            )}

            {tickets.length === 0 ? (
              <div className="py-12 text-center">
                <Headphones className="w-14 h-14 mx-auto mb-4 text-placeholder" />
                <h3 className="text-lg font-bold text-text-heading mb-2">No support enquiries yet</h3>
                <p className="text-muted mb-6 max-w-md mx-auto">
                  Need help? Contact our sales team or submit a new enquiry and we will respond as soon as possible.
                </p>
                <Link
                  href="/contact"
                  className="inline-flex items-center gap-2 px-6 py-3 bg-secondary-600 text-white font-semibold rounded-xl hover:opacity-90"
                >
                  <ArrowRight className="w-4 h-4" />
                  Contact Sales
                </Link>
              </div>
            ) : (
              <div className="space-y-4">
                {tickets.map((ticket) => (
                  <TicketCard key={ticket.id} ticket={ticket} />
                ))}
              </div>
            )}
          </div>
        </Container>
      </section>
    </>
  );
}
