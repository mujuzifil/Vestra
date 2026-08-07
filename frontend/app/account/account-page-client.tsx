"use client";

import { useEffect, useMemo } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import {
  User,
  FileText,
  Handshake,
  Bookmark,
  FolderOpen,
  HeadphonesIcon,
  Bell,
  Building2,
  Package,
  Phone,
  MapPin,
  History,
  Loader2,
  Activity,
  ArrowRight,
  Camera,
  CheckCircle2,
  Clock,
  AlertCircle,
  Edit3,
  Lock,
  Mail,
  SlidersHorizontal,
} from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useAddresses } from "@/hooks/use-addresses";
import { useActivity } from "@/hooks/use-activity";
import { useDistributorApplicationStatus } from "@/hooks/use-distributor-application-status";
import { useNotifications } from "@/hooks/use-notifications";
import { useAccountDashboard } from "@/hooks/use-account-dashboard";
import type { Address, ActivityItem } from "@/types";

const activityIcons: Record<string, React.ElementType> = {
  login: User,
  password_change: Lock,
  profile_update: Edit3,
  address_added: MapPin,
  address_updated: MapPin,
  address_deleted: MapPin,
  order_placed: FileText,
  order_paid: CheckCircle2,
  order_shipped: Clock,
  order_delivered: CheckCircle2,
  preference_update: SlidersHorizontal,
  account_deletion_requested: AlertCircle,
};

function StatCard({
  label,
  value,
  note,
  icon: Icon,
  color,
}: {
  label: string;
  value: React.ReactNode;
  note?: string;
  icon: React.ElementType;
  color: string;
}) {
  return (
    <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-5">
      <div className="flex items-start justify-between">
        <div className="min-w-0">
          <p className="text-sm text-muted">{label}</p>
          <p className="text-3xl font-extrabold text-text-heading mt-1">{value}</p>
          {note && <p className="text-xs text-placeholder mt-1">{note}</p>}
        </div>
        <div className={`p-2.5 rounded-xl ${color}`}>
          <Icon className="w-5 h-5 text-white" />
        </div>
      </div>
    </div>
  );
}

function ActivityIcon({ type }: { type: ActivityItem["type"] }) {
  const Icon = activityIcons[type] || Activity;
  return (
    <div className="w-8 h-8 rounded-full bg-secondary-50 text-secondary-600 flex items-center justify-center flex-shrink-0">
      <Icon className="w-4 h-4" />
    </div>
  );
}

export function AccountPageClient() {
  const router = useRouter();
  const { user, isAuthenticated, isLoading: authLoading } = useAuth();
  const { data: addresses, isLoading: addressesLoading } = useAddresses();
  const { data: activityData, isLoading: activityLoading } = useActivity(1);
  const { data: distributorStatus, isLoading: distributorLoading } = useDistributorApplicationStatus();
  const { data: notificationsData, isLoading: notificationsLoading } = useNotifications(1);
  const { data: dashboard, isLoading: dashboardLoading } = useAccountDashboard(isAuthenticated);

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  const savedAddresses = addresses?.slice(0, 2) || [];
  const recentActivity = activityData?.data?.slice(0, 5) || [];
  const unreadNotifications = notificationsData?.notifications?.length || 0;

  const distributorStatusText = distributorLoading
    ? "Loading..."
    : distributorStatus?.status
    ? `${distributorStatus.status.charAt(0).toUpperCase()}${distributorStatus.status.slice(1)}`
    : "Not submitted";

  const profileCompletion = useMemo(() => {
    if (!user) return 0;
    const fields = [
      user.name,
      user.first_name,
      user.last_name,
      user.phone,
      user.email,
      user.date_of_birth,
      user.gender,
      user.avatar_url,
    ];
    const filled = fields.filter(Boolean).length;
    return Math.round((filled / fields.length) * 100);
  }, [user]);

  if (authLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
      </div>
    );
  }

  if (!isAuthenticated || !user) {
    return null;
  }

  return (
    <>
      <PageHero
        title="Business Portal"
        subtitle={user ? `Welcome back, ${user.name}` : undefined}
        breadcrumb={[{ label: "Account" }]}
      />

      <section className="py-12 lg:py-20 bg-surface-page">
        <Container>
          <div className="space-y-8">
            {/* Welcome Panel */}
            <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6">
              <div className="flex flex-col sm:flex-row sm:items-center gap-4 mb-6">
                <div className="relative w-16 h-16 rounded-full overflow-hidden bg-secondary-50 border-2 border-surface-card shadow-md flex-shrink-0">
                  {user.avatar_url ? (
                    // eslint-disable-next-line @next/next/no-img-element -- avatar host is API origin
                    <img src={user.avatar_url} alt={user.name} className="absolute inset-0 w-full h-full object-cover" />
                  ) : (
                    <div className="w-full h-full flex items-center justify-center text-secondary-600">
                      <User className="w-8 h-8" />
                    </div>
                  )}
                </div>
                <div className="flex-1">
                  <div className="flex items-center gap-2 flex-wrap">
                    <h2 className="text-lg font-bold text-text-heading">{user.name}</h2>
                    <span className="px-2 py-0.5 text-xs font-semibold text-secondary-700 bg-secondary-100 rounded-full">
                      Business Account
                    </span>
                  </div>
                  <div className="flex items-center gap-2 text-sm text-muted mt-1">
                    <Mail className="w-3.5 h-3.5" />
                    {user.email}
                  </div>
                </div>
                <Link
                  href="/account/profile/photo"
                  className="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-secondary-600 bg-secondary-50 rounded-xl hover:bg-secondary-100 transition-colors-base"
                >
                  <Camera className="w-4 h-4" />
                  {user.avatar_url ? "Change Photo" : "Add Photo"}
                </Link>
              </div>

              <div className="space-y-2">
                <div className="flex items-center justify-between text-sm">
                  <span className="font-medium text-text-heading">Profile Completion</span>
                  <span className="font-bold text-secondary-600">{profileCompletion}%</span>
                </div>
                <div className="h-2.5 bg-neutral-100 rounded-full overflow-hidden">
                  <div
                    className="h-full bg-gradient-to-r from-secondary-500 to-secondary-600 rounded-full transition-all-base"
                    style={{ width: `${profileCompletion}%` }}
                  />
                </div>
                <p className="text-xs text-placeholder">
                  Complete your profile for faster quotations.
                </p>
              </div>
            </div>

            {/* Business Activity Summary */}
            <div>
              <h2 className="text-lg font-bold text-text-heading mb-4">Business Activity Summary</h2>
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <StatCard
                  label="Quote Requests"
                  value={dashboardLoading ? "..." : dashboard?.quotes.submitted ?? 0}
                  note={dashboard?.quotes.submitted ? `${dashboard.quotes.pending} pending` : "No quote requests yet"}
                  icon={FileText}
                  color="bg-primary-900"
                />
                <StatCard
                  label="Distributor Application"
                  value={distributorStatusText}
                  icon={Handshake}
                  color="bg-secondary-600"
                />
                <StatCard
                  label="Saved Products"
                  value={dashboardLoading ? "..." : dashboard?.saved_products ?? 0}
                  note={dashboard?.saved_products ? "Saved for future quotes" : "No saved products yet"}
                  icon={Bookmark}
                  color="bg-info-500"
                />
                <StatCard
                  label="Documents"
                  value={dashboardLoading ? "..." : dashboard?.documents ?? 0}
                  note={dashboard?.documents ? "Available for download" : "No documents yet"}
                  icon={FolderOpen}
                  color="bg-warning-500"
                />
                <StatCard
                  label="Support Enquiries"
                  value={dashboardLoading ? "..." : dashboard?.support_enquiries ?? 0}
                  note={dashboard?.support_enquiries ? "Open enquiries" : "No enquiries yet"}
                  icon={HeadphonesIcon}
                  color="bg-neutral-600"
                />
                <StatCard
                  label="Recent Notifications"
                  value={notificationsLoading ? "..." : unreadNotifications}
                  icon={Bell}
                  color="bg-primary-500"
                />
              </div>
            </div>

            {/* Distributor Status Banner */}
            {distributorStatus?.status && ["pending", "approved"].includes(distributorStatus.status) && (
              <div className="bg-secondary-50 border border-secondary-100 rounded-[20px] p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div className="flex items-start gap-3">
                  <div className="p-2 bg-secondary-100 rounded-xl">
                    <Handshake className="w-5 h-5 text-secondary-600" />
                  </div>
                  <div>
                    <p className="font-semibold text-text-heading">
                      Distributor Application{" "}
                      {distributorStatus.status === "pending" ? "Pending" : "Approved"}
                    </p>
                    <p className="text-sm text-muted">
                      {distributorStatus.status === "pending"
                        ? "Your application is under review. We will contact you shortly."
                        : "Your distributor application has been approved."}
                    </p>
                  </div>
                </div>
                <Link
                  href="/account/distributor"
                  className="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-secondary-600 rounded-xl hover:bg-secondary-700 transition-colors-base"
                >
                  View Application
                  <ArrowRight className="w-4 h-4" />
                </Link>
              </div>
            )}

            {/* Quick Actions */}
            <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6">
              <h2 className="text-lg font-bold text-text-heading mb-4">Quick Actions</h2>
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                {[
                  { icon: FileText, label: "Request a Quote", href: "/request-quote" },
                  { icon: Package, label: "Browse Products", href: "/products" },
                  { icon: Handshake, label: "Become a Distributor", href: "/distributor" },
                  { icon: Phone, label: "Contact Sales", href: "/contact" },
                  { icon: Building2, label: "Update Business Profile", href: "/account/company" },
                  { icon: FolderOpen, label: "View Documents", href: "/account/documents" },
                  { icon: HeadphonesIcon, label: "Support Centre", href: "/account/support" },
                  { icon: User, label: "Update Profile", href: "/account/profile" },
                ].map((action) => (
                  <Link
                    key={action.label}
                    href={action.href}
                    className="flex items-center gap-3 p-4 rounded-xl border border-default text-body hover:bg-surface-page transition-colors-base"
                  >
                    <div className="p-2 rounded-lg bg-secondary-50 text-secondary-600">
                      <action.icon className="w-5 h-5" />
                    </div>
                    <span className="font-medium text-sm">{action.label}</span>
                  </Link>
                ))}
              </div>
            </div>

            <div className="grid lg:grid-cols-2 gap-6">
              {/* Recent Activity Timeline */}
              <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6">
                <div className="flex items-center justify-between mb-4">
                  <h2 className="text-lg font-bold text-text-heading">Recent Activity</h2>
                  <Link href="/account/activity" className="text-sm font-semibold text-secondary-600 hover:text-secondary-700">
                    View All
                  </Link>
                </div>

                {activityLoading ? (
                  <div className="py-8 text-center">
                    <Loader2 className="w-6 h-6 animate-spin text-secondary-500 mx-auto" />
                  </div>
                ) : recentActivity.length === 0 ? (
                  <div className="py-8 text-center text-muted">
                    <History className="w-10 h-10 mx-auto mb-2 text-placeholder" />
                    <p>No recent activity.</p>
                  </div>
                ) : (
                  <div className="space-y-4">
                    {recentActivity.map((item) => (
                      <div key={item.id} className="flex items-start gap-3">
                        <ActivityIcon type={item.type} />
                        <div className="flex-1 min-w-0">
                          <p className="text-sm font-medium text-text-heading">{item.description}</p>
                          <p className="text-xs text-muted">{new Date(item.created_at).toLocaleString()}</p>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>

              {/* Saved Addresses Preview */}
              <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6">
                <div className="flex items-center justify-between mb-4">
                  <h2 className="text-lg font-bold text-text-heading">Saved Addresses</h2>
                  <Link href="/account/addresses" className="text-sm font-semibold text-secondary-600 hover:text-secondary-700">
                    Manage
                  </Link>
                </div>

                {addressesLoading ? (
                  <div className="py-8 text-center">
                    <Loader2 className="w-6 h-6 animate-spin text-secondary-500 mx-auto" />
                  </div>
                ) : savedAddresses.length === 0 ? (
                  <div className="py-8 text-center text-muted">
                    <MapPin className="w-10 h-10 mx-auto mb-2 text-placeholder" />
                    <p>No addresses saved.</p>
                    <Link href="/account/addresses" className="text-secondary-600 font-semibold hover:text-secondary-700">
                      Add Address
                    </Link>
                  </div>
                ) : (
                  <div className="space-y-3">
                    {savedAddresses.map((addr: Address) => (
                      <div key={addr.id} className="p-4 rounded-xl bg-surface-page">
                        <div className="flex items-center gap-2 mb-1">
                          <Building2 className="w-4 h-4 text-secondary-600" />
                          <span className="font-semibold text-text-heading">{addr.label}</span>
                          {addr.is_default && (
                            <span className="px-2 py-0.5 bg-secondary-100 text-secondary-600 text-xs font-medium rounded-full">
                              Default
                            </span>
                          )}
                        </div>
                        <p className="text-sm text-muted">{addr.full_name}</p>
                        <p className="text-sm text-muted">{addr.address_line}</p>
                        <p className="text-sm text-muted">
                          {addr.city}
                          {addr.region ? `, ${addr.region}` : ""}
                        </p>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>
          </div>
        </Container>
      </section>
    </>
  );
}
