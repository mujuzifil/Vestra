"use client";

import Link from "next/link";
import { MessageCircle } from "lucide-react";

const WHATSAPP_NUMBER = "256707128442";
const WHATSAPP_MESSAGE = encodeURIComponent(
  "Hello VESTRA,\n\nI would like to inquire about your fabric care products and services.\n\nKindly assist me with more information.\n\nThank you."
);

export function WhatsAppFloat() {
  return (
    <Link
      href={`https://wa.me/${WHATSAPP_NUMBER}?text=${WHATSAPP_MESSAGE}`}
      target="_blank"
      rel="noopener noreferrer"
      className="fixed bottom-6 right-6 z-[999] w-14 h-14 rounded-full bg-[#25d366] text-white flex items-center justify-center text-2xl shadow-lg hover:scale-110 hover:-translate-y-1 transition-all duration-300 group"
      aria-label="Chat with us on WhatsApp"
    >
      <MessageCircle className="w-7 h-7 fill-current" />
      <span className="absolute right-16 bg-white text-[#0a1628] px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap shadow-md opacity-0 translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all pointer-events-none hidden sm:block">
        Chat with us
      </span>
    </Link>
  );
}
