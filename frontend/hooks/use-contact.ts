"use client";

import { useMutation } from "@tanstack/react-query";
import { submitContact } from "@/lib/api/contact";
import type { ContactFormData } from "@/types";

export function useContactMutation() {
  return useMutation({
    mutationFn: (data: ContactFormData) => submitContact(data),
  });
}
