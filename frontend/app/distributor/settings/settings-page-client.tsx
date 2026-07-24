"use client";

import { useState } from "react";
import { Loader2, Save, Lock, Globe } from "lucide-react";
import { InputField } from "@/components/common/form-field";
import { useDistributorProfile } from "@/hooks/use-distributor-profile";
import { toastSuccess, toastError } from "@/lib/toast-utils";
import { cn } from "@/lib/utils";
import type { Distributor } from "@/types";

export function SettingsPageClient() {
  const { data: distributor, isLoading, update } = useDistributorProfile();
  const [form, setForm] = useState<Partial<Distributor>>({});
  const [isSaving, setIsSaving] = useState(false);
  const [passwords, setPasswords] = useState({ current: "", new: "", confirm: "" });

  if (isLoading || !distributor) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-green-500" />
      </div>
    );
  }

  const handleChange = (field: keyof typeof distributor, value: string) => {
    setForm((prev) => ({ ...prev, [field]: value }));
  };

  async function handleSaveProfile(e: React.FormEvent) {
    e.preventDefault();
    setIsSaving(true);
    try {
      await update(form);
      setForm({});
      toastSuccess("Settings saved.");
    } catch (err) {
      toastError(err instanceof Error ? err.message : "Save failed.");
    } finally {
      setIsSaving(false);
    }
  }

  async function handleChangePassword(e: React.FormEvent) {
    e.preventDefault();
    if (passwords.new !== passwords.confirm) {
      toastError("New passwords do not match.");
      return;
    }
    toastSuccess("Password change request submitted.");
    setPasswords({ current: "", new: "", confirm: "" });
  }

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl font-extrabold text-[#0a1628]">Settings</h1>
        <p className="text-[#64748b]">Manage your distributor portal preferences.</p>
      </div>

      <form onSubmit={handleSaveProfile} className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 space-y-6">
        <div className="flex items-center gap-3 mb-2">
          <div className="p-2 rounded-xl bg-green-600">
            <Globe className="w-5 h-5 text-white" />
          </div>
          <h2 className="text-lg font-bold text-[#0a1628]">Portal Preferences</h2>
        </div>
        <div className="grid sm:grid-cols-2 gap-5">
          <InputField
            id="notification_email"
            label="Notification Email"
            type="email"
            value={form.email ?? distributor.email}
            onChange={(e) => handleChange("email", e.target.value)}
          />
          <InputField
            id="primary_contact_name"
            label="Primary Contact"
            value={form.primary_contact_name ?? distributor.primary_contact_name ?? ""}
            onChange={(e) => handleChange("primary_contact_name", e.target.value)}
          />
        </div>
        <div className="flex justify-end">
          <button
            type="submit"
            disabled={isSaving}
            className={cn(
              "inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors",
              isSaving && "opacity-70 cursor-not-allowed"
            )}
          >
            {isSaving && <Loader2 className="w-4 h-4 animate-spin" />}
            <Save className="w-4 h-4" />
            {isSaving ? "Saving..." : "Save Preferences"}
          </button>
        </div>
      </form>

      <form onSubmit={handleChangePassword} className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 space-y-6">
        <div className="flex items-center gap-3 mb-2">
          <div className="p-2 rounded-xl bg-[#0a1628]">
            <Lock className="w-5 h-5 text-white" />
          </div>
          <h2 className="text-lg font-bold text-[#0a1628]">Change Password</h2>
        </div>
        <div className="grid sm:grid-cols-2 gap-5">
          <InputField
            id="current-password"
            label="Current Password"
            type="password"
            value={passwords.current}
            onChange={(e) => setPasswords((p) => ({ ...p, current: e.target.value }))}
          />
          <InputField
            id="new-password"
            label="New Password"
            type="password"
            value={passwords.new}
            onChange={(e) => setPasswords((p) => ({ ...p, new: e.target.value }))}
          />
          <InputField
            id="confirm-password"
            label="Confirm New Password"
            type="password"
            value={passwords.confirm}
            onChange={(e) => setPasswords((p) => ({ ...p, confirm: e.target.value }))}
          />
        </div>
        <div className="flex justify-end">
          <button
            type="submit"
            className="inline-flex items-center gap-2 px-5 py-2.5 bg-[#0a1628] text-white font-semibold rounded-xl hover:bg-[#1a2638] transition-colors"
          >
            <Lock className="w-4 h-4" />
            Change Password
          </button>
        </div>
      </form>
    </div>
  );
}
