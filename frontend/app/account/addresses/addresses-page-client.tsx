"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { MapPin, Plus, ChevronLeft, Loader2, Trash2, Pencil, Home } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useAddresses } from "@/hooks/use-addresses";
import type { Address } from "@/types";

export function AddressesPageClient() {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();
  const { data: addresses, isLoading, create, update, remove } = useAddresses();
  const [showForm, setShowForm] = useState(false);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [form, setForm] = useState({
    label: "",
    full_name: "",
    phone: "",
    city: "",
    region: "",
    district: "",
    address_line: "",
    address_line_2: "",
    postal_code: "",
    country: "Uganda",
    delivery_notes: "",
    is_default: false,
    is_default_shipping: false,
    is_default_billing: false,
  });
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  const resetForm = () => {
    setForm({
      label: "",
      full_name: "",
      phone: "",
      city: "",
      region: "",
      district: "",
      address_line: "",
      address_line_2: "",
      postal_code: "",
      country: "Uganda",
      delivery_notes: "",
      is_default: false,
      is_default_shipping: false,
      is_default_billing: false,
    });
    setEditingId(null);
    setShowForm(false);
  };

  const handleEdit = (addr: Address) => {
    setForm({
      label: addr.label,
      full_name: addr.full_name,
      phone: addr.phone,
      city: addr.city,
      region: addr.region || "",
      district: addr.district || "",
      address_line: addr.address_line,
      address_line_2: addr.address_line_2 || "",
      postal_code: addr.postal_code || "",
      country: addr.country || "Uganda",
      delivery_notes: addr.delivery_notes || "",
      is_default: addr.is_default,
      is_default_shipping: addr.is_default_shipping,
      is_default_billing: addr.is_default_billing,
    });
    setEditingId(addr.id);
    setShowForm(true);
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitting(true);
    try {
      if (editingId) {
        await update({ id: editingId, data: form });
      } else {
        await create(form);
      }
      resetForm();
    } finally {
      setSubmitting(false);
    }
  };

  if (authLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-green-500" />
      </div>
    );
  }

  if (!isAuthenticated) return null;

  return (
    <>
      <PageHero
        title="My Addresses"
        subtitle="Manage your delivery addresses"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Addresses" }]}
      />

      <section className="py-12 lg:py-20 bg-[#f8fafc]">
        <Container>
          <Link
            href="/account"
            className="inline-flex items-center gap-2 text-sm font-semibold text-[#64748b] hover:text-[#0a1628] mb-6"
          >
            <ChevronLeft className="w-4 h-4" />
            Back to Account
          </Link>

          {/* Add/Edit Form */}
          {showForm && (
            <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 lg:p-8 mb-8">
              <h2 className="text-lg font-bold text-[#0a1628] mb-4">
                {editingId ? "Edit Address" : "Add New Address"}
              </h2>
              <form onSubmit={handleSubmit} className="grid sm:grid-cols-2 gap-4">
                <div className="sm:col-span-2">
                  <label className="block text-sm font-medium text-[#0a1628] mb-1">Label</label>
                  <input
                    type="text"
                    required
                    placeholder="e.g. Home, Office"
                    value={form.label}
                    onChange={(e) => setForm({ ...form, label: e.target.value })}
                    className="w-full px-4 py-2.5 rounded-xl border border-[#e2e8f0] focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-[#0a1628] mb-1">Full Name</label>
                  <input
                    type="text"
                    required
                    value={form.full_name}
                    onChange={(e) => setForm({ ...form, full_name: e.target.value })}
                    className="w-full px-4 py-2.5 rounded-xl border border-[#e2e8f0] focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-[#0a1628] mb-1">Phone</label>
                  <input
                    type="tel"
                    required
                    value={form.phone}
                    onChange={(e) => setForm({ ...form, phone: e.target.value })}
                    className="w-full px-4 py-2.5 rounded-xl border border-[#e2e8f0] focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-[#0a1628] mb-1">City</label>
                  <input
                    type="text"
                    required
                    value={form.city}
                    onChange={(e) => setForm({ ...form, city: e.target.value })}
                    className="w-full px-4 py-2.5 rounded-xl border border-[#e2e8f0] focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-[#0a1628] mb-1">Region</label>
                  <input
                    type="text"
                    value={form.region}
                    onChange={(e) => setForm({ ...form, region: e.target.value })}
                    className="w-full px-4 py-2.5 rounded-xl border border-[#e2e8f0] focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-[#0a1628] mb-1">District</label>
                  <input
                    type="text"
                    value={form.district}
                    onChange={(e) => setForm({ ...form, district: e.target.value })}
                    className="w-full px-4 py-2.5 rounded-xl border border-[#e2e8f0] focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none"
                  />
                </div>
                <div className="sm:col-span-2">
                  <label className="block text-sm font-medium text-[#0a1628] mb-1">Address Line</label>
                  <textarea
                    required
                    rows={2}
                    value={form.address_line}
                    onChange={(e) => setForm({ ...form, address_line: e.target.value })}
                    className="w-full px-4 py-2.5 rounded-xl border border-[#e2e8f0] focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none resize-none"
                  />
                </div>
                <div className="sm:col-span-2">
                  <label className="block text-sm font-medium text-[#0a1628] mb-1">Apartment / Suite / Floor (optional)</label>
                  <input
                    type="text"
                    value={form.address_line_2}
                    onChange={(e) => setForm({ ...form, address_line_2: e.target.value })}
                    className="w-full px-4 py-2.5 rounded-xl border border-[#e2e8f0] focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none"
                  />
                </div>
                <div className="grid sm:grid-cols-2 gap-4 sm:col-span-2">
                  <div>
                    <label className="block text-sm font-medium text-[#0a1628] mb-1">Postal Code (optional)</label>
                    <input
                      type="text"
                      value={form.postal_code}
                      onChange={(e) => setForm({ ...form, postal_code: e.target.value })}
                      className="w-full px-4 py-2.5 rounded-xl border border-[#e2e8f0] focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-[#0a1628] mb-1">Country</label>
                    <input
                      type="text"
                      required
                      value={form.country}
                      onChange={(e) => setForm({ ...form, country: e.target.value })}
                      className="w-full px-4 py-2.5 rounded-xl border border-[#e2e8f0] focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none"
                    />
                  </div>
                </div>
                <div className="sm:col-span-2">
                  <label className="block text-sm font-medium text-[#0a1628] mb-1">Delivery Notes (optional)</label>
                  <textarea
                    rows={2}
                    value={form.delivery_notes}
                    onChange={(e) => setForm({ ...form, delivery_notes: e.target.value })}
                    placeholder="Gate code, landmark, delivery instructions..."
                    className="w-full px-4 py-2.5 rounded-xl border border-[#e2e8f0] focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none resize-none"
                  />
                </div>
                <div className="sm:col-span-2 grid sm:grid-cols-3 gap-3">
                  <label className="flex items-center gap-2 p-3 rounded-xl border border-[#e2e8f0] cursor-pointer hover:bg-[#f8fafc]">
                    <input
                      type="checkbox"
                      checked={form.is_default}
                      onChange={(e) => setForm({ ...form, is_default: e.target.checked })}
                      className="w-4 h-4 rounded border-[#e2e8f0] text-green-600 focus:ring-green-500"
                    />
                    <span className="text-sm text-[#64748b]">Default</span>
                  </label>
                  <label className="flex items-center gap-2 p-3 rounded-xl border border-[#e2e8f0] cursor-pointer hover:bg-[#f8fafc]">
                    <input
                      type="checkbox"
                      checked={form.is_default_shipping}
                      onChange={(e) => setForm({ ...form, is_default_shipping: e.target.checked })}
                      className="w-4 h-4 rounded border-[#e2e8f0] text-green-600 focus:ring-green-500"
                    />
                    <span className="text-sm text-[#64748b]">Default Shipping</span>
                  </label>
                  <label className="flex items-center gap-2 p-3 rounded-xl border border-[#e2e8f0] cursor-pointer hover:bg-[#f8fafc]">
                    <input
                      type="checkbox"
                      checked={form.is_default_billing}
                      onChange={(e) => setForm({ ...form, is_default_billing: e.target.checked })}
                      className="w-4 h-4 rounded border-[#e2e8f0] text-green-600 focus:ring-green-500"
                    />
                    <span className="text-sm text-[#64748b]">Default Billing</span>
                  </label>
                </div>
                <div className="sm:col-span-2 flex gap-3">
                  <button
                    type="submit"
                    disabled={submitting}
                    className="px-6 py-2.5 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors disabled:opacity-50"
                  >
                    {submitting ? <Loader2 className="w-4 h-4 animate-spin" /> : editingId ? "Update Address" : "Save Address"}
                  </button>
                  <button
                    type="button"
                    onClick={resetForm}
                    className="px-6 py-2.5 border border-[#e2e8f0] text-[#64748b] font-semibold rounded-xl hover:bg-[#f8fafc] transition-colors"
                  >
                    Cancel
                  </button>
                </div>
              </form>
            </div>
          )}

          {/* Address List */}
          <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 lg:p-8">
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-lg font-bold text-[#0a1628]">Saved Addresses</h2>
              {!showForm && (
                <button
                  onClick={() => setShowForm(true)}
                  className="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-xl hover:bg-green-700 transition-colors"
                >
                  <Plus className="w-4 h-4" />
                  Add Address
                </button>
              )}
            </div>

            {isLoading ? (
              <div className="py-12 text-center">
                <Loader2 className="w-8 h-8 animate-spin text-green-500 mx-auto" />
              </div>
            ) : !addresses || addresses.length === 0 ? (
              <div className="py-16 text-center">
                <MapPin className="w-14 h-14 mx-auto mb-4 text-[#94a3b8]" />
                <h3 className="text-lg font-bold text-[#0a1628] mb-2">No addresses saved</h3>
                <p className="text-[#64748b] mb-6">Add a delivery address for faster checkout.</p>
                {!showForm && (
                  <button
                    onClick={() => setShowForm(true)}
                    className="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors"
                  >
                    <Plus className="w-4 h-4" />
                    Add Address
                  </button>
                )}
              </div>
            ) : (
              <div className="grid sm:grid-cols-2 gap-4">
                {addresses.map((addr) => (
                  <div
                    key={addr.id}
                    className={`p-5 rounded-xl border ${
                      addr.is_default
                        ? "border-green-500 bg-green-50/50"
                        : "border-[#e2e8f0] bg-[#f8fafc]"
                    }`}
                  >
                    <div className="flex items-start justify-between mb-3">
                      <div className="flex flex-wrap items-center gap-2">
                        <Home className="w-4 h-4 text-green-600" />
                        <span className="font-semibold text-[#0a1628]">{addr.label}</span>
                        {addr.is_default && (
                          <span className="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                            Default
                          </span>
                        )}
                        {addr.is_default_shipping && (
                          <span className="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                            Shipping
                          </span>
                        )}
                        {addr.is_default_billing && (
                          <span className="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs font-medium rounded-full">
                            Billing
                          </span>
                        )}
                      </div>
                      <div className="flex gap-1">
                        <button
                          onClick={() => handleEdit(addr)}
                          className="p-1.5 rounded-lg text-[#64748b] hover:bg-white hover:text-green-600 transition-colors"
                          title="Edit"
                        >
                          <Pencil className="w-4 h-4" />
                        </button>
                        <button
                          onClick={() => remove(addr.id)}
                          className="p-1.5 rounded-lg text-[#64748b] hover:bg-white hover:text-red-600 transition-colors"
                          title="Delete"
                        >
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </div>
                    </div>
                    <div className="text-sm text-[#64748b] space-y-0.5">
                      <p className="font-medium text-[#0a1628]">{addr.full_name}</p>
                      <p>{addr.phone}</p>
                      <p>{addr.address_line}</p>
                      {addr.address_line_2 && <p>{addr.address_line_2}</p>}
                      <p>
                        {addr.city}
                        {addr.region ? `, ${addr.region}` : ""}
                        {addr.district ? `, ${addr.district}` : ""}
                        {addr.postal_code ? ` · ${addr.postal_code}` : ""}
                      </p>
                      {addr.country && <p>{addr.country}</p>}
                      {addr.delivery_notes && <p className="text-xs text-[#94a3b8] mt-1">{addr.delivery_notes}</p>}
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </Container>
      </section>
    </>
  );
}
