"use client";

import { useState } from "react";
import { Users, Plus, Loader2, Trash2, Pencil, Star } from "lucide-react";
import { InputField } from "@/components/common/form-field";
import { EmptyState } from "@/components/common/empty-state";
import { useDistributorContacts, type CreateContactData } from "@/hooks/use-distributor-contacts";
import { toastSuccess, toastError } from "@/lib/toast-utils";
import { cn } from "@/lib/utils";
import type { DistributorContact } from "@/types";

const emptyContact: CreateContactData = {
  name: "",
  role: "",
  phone: "",
  email: "",
  permissions_json: [],
  is_primary: false,
};

export function ContactsPageClient() {
  const { data: contacts, isLoading, create, update, remove } = useDistributorContacts();
  const [isEditing, setIsEditing] = useState(false);
  const [form, setForm] = useState<Partial<DistributorContact>>(emptyContact);
  const [isSubmitting, setIsSubmitting] = useState(false);

  function openCreate() {
    setForm(emptyContact);
    setIsEditing(true);
  }

  function openEdit(contact: DistributorContact) {
    setForm(contact);
    setIsEditing(true);
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setIsSubmitting(true);
    try {
      if (form.id) {
        await update({ id: form.id, data: form });
        toastSuccess("Contact updated.");
      } else {
        await create(form as CreateContactData);
        toastSuccess("Contact created.");
      }
      setIsEditing(false);
      setForm(emptyContact);
    } catch (err) {
      toastError(err instanceof Error ? err.message : "Request failed.");
    } finally {
      setIsSubmitting(false);
    }
  }

  async function handleDelete(id: number) {
    if (!confirm("Are you sure you want to delete this contact?")) return;
    try {
      await remove(id);
      toastSuccess("Contact deleted.");
    } catch (err) {
      toastError(err instanceof Error ? err.message : "Delete failed.");
    }
  }

  if (isLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-green-500" />
      </div>
    );
  }

  return (
    <div className="space-y-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-[#0a1628]">Contacts</h1>
          <p className="text-[#64748b]">Manage people associated with your distributor account.</p>
        </div>
        <button
          type="button"
          onClick={openCreate}
          className="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors"
        >
          <Plus className="w-4 h-4" />
          Add Contact
        </button>
      </div>

      {isEditing && (
        <form onSubmit={handleSubmit} className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 space-y-5">
          <h2 className="text-lg font-bold text-[#0a1628]">{form.id ? "Edit Contact" : "New Contact"}</h2>
          <div className="grid sm:grid-cols-2 gap-5">
            <InputField
              id="name"
              label="Name"
              value={form.name ?? ""}
              onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))}
              required
            />
            <InputField
              id="role"
              label="Role"
              value={form.role ?? ""}
              onChange={(e) => setForm((f) => ({ ...f, role: e.target.value }))}
            />
            <InputField
              id="phone"
              label="Phone"
              type="tel"
              value={form.phone ?? ""}
              onChange={(e) => setForm((f) => ({ ...f, phone: e.target.value }))}
            />
            <InputField
              id="email"
              label="Email"
              type="email"
              value={form.email ?? ""}
              onChange={(e) => setForm((f) => ({ ...f, email: e.target.value }))}
            />
          </div>
          <label className="flex items-center gap-2 text-sm text-[#0a1628]">
            <input
              type="checkbox"
              checked={form.is_primary ?? false}
              onChange={(e) => setForm((f) => ({ ...f, is_primary: e.target.checked }))}
              className="w-4 h-4 rounded border-[#e2e8f0] text-green-600 focus:ring-green-500"
            />
            Set as primary contact
          </label>
          <div className="flex items-center gap-3 pt-2">
            <button
              type="submit"
              disabled={isSubmitting}
              className={cn(
                "inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors",
                isSubmitting && "opacity-70 cursor-not-allowed"
              )}
            >
              {isSubmitting && <Loader2 className="w-4 h-4 animate-spin" />}
              {form.id ? "Update Contact" : "Create Contact"}
            </button>
            <button
              type="button"
              onClick={() => setIsEditing(false)}
              className="px-5 py-2.5 text-[#64748b] font-semibold hover:text-[#0a1628]"
            >
              Cancel
            </button>
          </div>
        </form>
      )}

      {!contacts || contacts.length === 0 ? (
        <EmptyState title="No contacts yet" description="Add contacts to keep your distributor account organized." />
      ) : (
        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
          {contacts.map((contact) => (
            <div key={contact.id} className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-5">
              <div className="flex items-start justify-between mb-3">
                <div className="flex items-center gap-2">
                  <Users className="w-5 h-5 text-green-600" />
                  <h3 className="font-bold text-[#0a1628]">{contact.name}</h3>
                </div>
                {contact.is_primary && (
                  <span className="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                    <Star className="w-3 h-3" />
                    Primary
                  </span>
                )}
              </div>
              <div className="text-sm text-[#64748b] space-y-1 mb-4">
                <p>{contact.role || "No role"}</p>
                <p>{contact.phone || "—"}</p>
                <p>{contact.email || "—"}</p>
              </div>
              <div className="flex items-center gap-2">
                <button
                  type="button"
                  onClick={() => openEdit(contact)}
                  className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-green-700 bg-green-50 rounded-lg hover:bg-green-100"
                >
                  <Pencil className="w-3 h-3" />
                  Edit
                </button>
                <button
                  type="button"
                  onClick={() => handleDelete(contact.id)}
                  className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 rounded-lg hover:bg-red-100"
                >
                  <Trash2 className="w-3 h-3" />
                  Delete
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
