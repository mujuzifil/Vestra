"use client";

import { useState } from "react";
import { MapPin, Plus, Loader2, Trash2, Pencil, Star } from "lucide-react";
import { InputField, TextareaField } from "@/components/common/form-field";
import { EmptyState } from "@/components/common/empty-state";
import { useDistributorBranches, type CreateBranchData } from "@/hooks/use-distributor-branches";
import { toastSuccess, toastError } from "@/lib/toast-utils";
import { cn } from "@/lib/utils";
import type { DistributorBranch } from "@/types";

const emptyBranch: CreateBranchData = {
  name: "",
  manager_name: "",
  phone: "",
  email: "",
  country: "",
  district: "",
  city: "",
  address: "",
  latitude: null,
  longitude: null,
  delivery_notes: "",
  status: "active",
  is_default: false,
};

export function BranchesPageClient() {
  const { data: branches, isLoading, create, update, remove } = useDistributorBranches();
  const [isEditing, setIsEditing] = useState(false);
  const [form, setForm] = useState<Partial<DistributorBranch>>(emptyBranch);
  const [isSubmitting, setIsSubmitting] = useState(false);

  function openCreate() {
    setForm(emptyBranch);
    setIsEditing(true);
  }

  function openEdit(branch: DistributorBranch) {
    setForm(branch);
    setIsEditing(true);
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setIsSubmitting(true);
    try {
      if (form.id) {
        await update({ id: form.id, data: form });
        toastSuccess("Branch updated.");
      } else {
        await create(form as CreateBranchData);
        toastSuccess("Branch created.");
      }
      setIsEditing(false);
      setForm(emptyBranch);
    } catch (err) {
      toastError(err instanceof Error ? err.message : "Request failed.");
    } finally {
      setIsSubmitting(false);
    }
  }

  async function handleDelete(id: number) {
    if (!confirm("Are you sure you want to delete this branch?")) return;
    try {
      await remove(id);
      toastSuccess("Branch deleted.");
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
          <h1 className="text-2xl font-extrabold text-[#0a1628]">Branches</h1>
          <p className="text-[#64748b]">Manage distribution branches and locations.</p>
        </div>
        <button
          type="button"
          onClick={openCreate}
          className="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors"
        >
          <Plus className="w-4 h-4" />
          Add Branch
        </button>
      </div>

      {isEditing && (
        <form onSubmit={handleSubmit} className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 space-y-5">
          <h2 className="text-lg font-bold text-[#0a1628]">{form.id ? "Edit Branch" : "New Branch"}</h2>
          <div className="grid sm:grid-cols-2 gap-5">
            <InputField
              id="name"
              label="Branch Name"
              value={form.name ?? ""}
              onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))}
              required
            />
            <InputField
              id="manager_name"
              label="Manager Name"
              value={form.manager_name ?? ""}
              onChange={(e) => setForm((f) => ({ ...f, manager_name: e.target.value }))}
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
            <InputField
              id="country"
              label="Country"
              value={form.country ?? ""}
              onChange={(e) => setForm((f) => ({ ...f, country: e.target.value }))}
            />
            <InputField
              id="district"
              label="District"
              value={form.district ?? ""}
              onChange={(e) => setForm((f) => ({ ...f, district: e.target.value }))}
            />
            <InputField
              id="city"
              label="City"
              value={form.city ?? ""}
              onChange={(e) => setForm((f) => ({ ...f, city: e.target.value }))}
            />
            <InputField
              id="status"
              label="Status"
              value={form.status ?? "active"}
              onChange={(e) => setForm((f) => ({ ...f, status: e.target.value }))}
            />
          </div>
          <TextareaField
            id="address"
            label="Address"
            value={form.address ?? ""}
            onChange={(e) => setForm((f) => ({ ...f, address: e.target.value }))}
          />
          <TextareaField
            id="delivery_notes"
            label="Delivery Notes"
            value={form.delivery_notes ?? ""}
            onChange={(e) => setForm((f) => ({ ...f, delivery_notes: e.target.value }))}
          />
          <label className="flex items-center gap-2 text-sm text-[#0a1628]">
            <input
              type="checkbox"
              checked={form.is_default ?? false}
              onChange={(e) => setForm((f) => ({ ...f, is_default: e.target.checked }))}
              className="w-4 h-4 rounded border-[#e2e8f0] text-green-600 focus:ring-green-500"
            />
            Set as default branch
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
              {form.id ? "Update Branch" : "Create Branch"}
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

      {!branches || branches.length === 0 ? (
        <EmptyState
          title="No branches yet"
          description="Add your first distribution branch to manage deliveries and contacts."
        />
      ) : (
        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
          {branches.map((branch) => (
            <div key={branch.id} className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-5">
              <div className="flex items-start justify-between mb-3">
                <div className="flex items-center gap-2">
                  <MapPin className="w-5 h-5 text-green-600" />
                  <h3 className="font-bold text-[#0a1628]">{branch.name}</h3>
                </div>
                {branch.is_default && (
                  <span className="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                    <Star className="w-3 h-3" />
                    Default
                  </span>
                )}
              </div>
              <div className="text-sm text-[#64748b] space-y-1 mb-4">
                <p>{branch.manager_name || "No manager assigned"}</p>
                <p>{branch.phone || branch.email || "—"}</p>
                <p>{[branch.address, branch.city, branch.district].filter(Boolean).join(", ")}</p>
              </div>
              <div className="flex items-center gap-2">
                <button
                  type="button"
                  onClick={() => openEdit(branch)}
                  className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-green-700 bg-green-50 rounded-lg hover:bg-green-100"
                >
                  <Pencil className="w-3 h-3" />
                  Edit
                </button>
                <button
                  type="button"
                  onClick={() => handleDelete(branch.id)}
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
