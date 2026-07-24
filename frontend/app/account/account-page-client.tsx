"use client";

import { useEffect, useMemo } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import Image from "next/image";
import {
  User,
  Package,
  MapPin,
  LogOut,
  ChevronRight,
  Loader2,
  ShoppingBag,
  CreditCard,
  Truck,
  CheckCircle2,
  Search,
  Settings,
  ArrowRight,
  Camera,
  Activity,
  Home,
  AlertCircle,
} from "lucide-react";
import { FeedbackForm } from "@/components/feedback/feedback-form";
import { submitFeedback } from "@/lib/api/feedback";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useOrders } from "@/hooks/use-orders";
import { useAddresses } from "@/hooks/use-addresses";
import { useActivity } from "@/hooks/use-activity";
import type { Order, Address } from "@/types";

const statusColors: Record<string, string> = {
  pending: "bg-warning-100 text-warning-600",
  paid: "bg-success-100 text-success-600",
  processing: "bg-info-100 text-info-600",
  packed: "bg-indigo-100 text-indigo-700",
  shipped: "bg-cyan-100 text-cyan-700",
  delivered: "bg-secondary-100 text-secondary-600",
  cancelled: "bg-danger-100 text-danger-600",
  refunded: "bg-neutral-100 text-neutral-600",
};

function StatCard({
  label,
  value,
  icon: Icon,
  color,
}: {
  label: string;
  value: number;
  icon: React.ElementType;
  color: string;
}) {
  return (
    <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-5">
      <div className="flex items-start justify-between">
        <div>
          <p className="text-sm text-muted">{label}</p>
          <p className="text-3xl font-extrabold text-primary-900 mt-1">{value}</p>
        </div>
        <div className={`p-2.5 rounded-xl ${color}`}>
          <Icon className="w-5 h-5 text-white" />
        </div>
      </div>
    </div>
  );
}

function OrderRow({ order }: { order: Order }) {
  const needsPayment = order.payment_status !== "paid" && order.status !== "cancelled" && order.status !== "refunded";
  const isShipped = order.status === "shipped" || order.status === "delivered";

  return (
    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl bg-surface-page">
      <div>
        <p className="font-semibold text-primary-900">{order.invoice_number}</p>
        <p className="text-sm text-muted">{new Date(order.created_at).toLocaleDateString()}</p>
      </div>
      <div className="flex items-center gap-3">
        <span
          className={`inline-block px-2.5 py-0.5 rounded-full text-xs font-medium capitalize ${
            statusColors[order.status] || "bg-neutral-100 text-neutral-600"
          }`}
        >
          {order.status}
        </span>
        <span className="font-bold text-primary-500">UGX {order.total_amount}</span>
      </div>
      <div className="flex items-center gap-2">
        {needsPayment && (
          <Link
            href={`/account/orders/${order.id}`}
            className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-secondary-600 rounded-lg hover:bg-secondary-600"
          >
            <CreditCard className="w-3 h-3" />
            Pay
          </Link>
        )}
        {isShipped && (
          <Link
            href={`/track?invoice=${encodeURIComponent(order.invoice_number)}`}
            className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-secondary-600 bg-secondary-100 rounded-lg hover:bg-secondary-200"
          >
            <Truck className="w-3 h-3" />
            Track
          </Link>
        )}
        <Link
          href={`/account/orders/${order.id}`}
          className="inline-flex items-center gap-1.5 text-sm font-semibold text-secondary-600 hover:text-secondary-600"
        >
          View
          <ArrowRight className="w-3 h-3" />
        </Link>
      </div>
    </div>
  );
}

export function AccountPageClient() {
  const router = useRouter();
  const { user, isAuthenticated, isLoading: authLoading, logout } = useAuth();
  const { data: orders, isLoading: ordersLoading } = useOrders();
  const { data: addresses, isLoading: addressesLoading } = useAddresses();
  const { data: activityData, isLoading: activityLoading } = useActivity(1);

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  const stats = useMemo(() => {
    const all = orders || [];
    return {
      total: all.length,
      pendingPayment: all.filter(
        (o) => o.payment_status !== "paid" && o.status !== "cancelled" && o.status !== "refunded"
      ).length,
      processing: all.filter((o) => ["paid", "processing", "packed", "shipped"].includes(o.status)).length,
      completed: all.filter((o) => o.status === "delivered").length,
      cancelled: all.filter((o) => o.status === "cancelled" || o.status === "refunded").length,
    };
  }, [orders]);

  const recentOrders = orders?.slice(0, 5) || [];
  const savedAddresses = addresses?.slice(0, 2) || [];
  const recentActivity = activityData?.data?.slice(0, 3) || [];

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

  const menuItems = [
    { icon: Package, label: "My Orders", href: "/account/orders", description: "View and track your orders" },
    { icon: MapPin, label: "Addresses", href: "/account/addresses", description: "Manage delivery addresses" },
    { icon: Settings, label: "Settings", href: "/account/settings", description: "Update profile and password" },
  ];

  const quickActions = [
    { icon: ShoppingBag, label: "Continue Shopping", href: "/products", color: "bg-secondary-600" },
    { icon: Search, label: "Track Order", href: "/track", color: "bg-primary-500" },
    { icon: Package, label: "View Orders", href: "/account/orders", color: "bg-info-600" },
    { icon: Settings, label: "Update Profile", href: "/account/settings", color: "bg-neutral-600" },
  ];

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
      <PageHero title="My Account" subtitle={`Welcome back, ${user.name}`} breadcrumb={[{ label: "Account" }]} />

      <section className="py-12 lg:py-20 bg-surface-page">
        <Container>
          <div className="grid lg:grid-cols-4 gap-8">
            {/* Sidebar */}
            <div className="lg:col-span-1">
              <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 sticky top-24">
                <div className="flex items-center gap-4 mb-6 pb-6 border-b border-default">
                  <div className="w-12 h-12 rounded-full bg-secondary-500/10 text-secondary-600 flex items-center justify-center">
                    <User className="w-6 h-6" />
                  </div>
                  <div>
                    <p className="font-bold text-primary-900">{user.name}</p>
                    <p className="text-sm text-muted">{user.email}</p>
                  </div>
                </div>

                <nav className="space-y-2">
                  {menuItems.map((item) => (
                    <Link
                      key={item.href}
                      href={item.href}
                      className="flex items-center gap-3 p-3 rounded-xl text-body hover:bg-surface-page hover:text-primary-900 transition-colors-base"
                    >
                      <item.icon className="w-5 h-5" />
                      <span className="font-medium">{item.label}</span>
                      <ChevronRight className="w-4 h-4 ml-auto" />
                    </Link>
                  ))}
                  <button
                    onClick={() => logout().then(() => router.push("/"))}
                    className="w-full flex items-center gap-3 p-3 rounded-xl text-danger-600 hover:bg-danger-50 transition-colors-base"
                  >
                    <LogOut className="w-5 h-5" />
                    <span className="font-medium">Sign Out</span>
                  </button>
                </nav>
              </div>
            </div>

            {/* Main Content */}
            <div className="lg:col-span-3 space-y-8">
              {/* Welcome + Profile Completion */}
              <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6">
                <div className="flex flex-col sm:flex-row sm:items-center gap-4 mb-6">
                  <div className="relative w-16 h-16 rounded-full overflow-hidden bg-secondary-50 border-2 border-surface-card shadow-md flex-shrink-0">
                    {user.avatar_url ? (
                      <Image src={user.avatar_url} alt={user.name} fill className="object-cover" sizes="64px" />
                    ) : (
                      <div className="w-full h-full flex items-center justify-center text-secondary-600">
                        <User className="w-8 h-8" />
                      </div>
                    )}
                  </div>
                  <div className="flex-1">
                    <h2 className="text-lg font-bold text-primary-900">Welcome back, {user.name}</h2>
                    <p className="text-sm text-muted">{user.email}</p>
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
                    <span className="font-medium text-primary-900">Profile Completion</span>
                    <span className="font-bold text-secondary-600">{profileCompletion}%</span>
                  </div>
                  <div className="h-2.5 bg-neutral-100 rounded-full overflow-hidden">
                    <div
                      className="h-full bg-gradient-to-r from-secondary-500 to-secondary-600 rounded-full transition-all-base"
                      style={{ width: `${profileCompletion}%` }}
                    />
                  </div>
                  <p className="text-xs text-placeholder">
                    Complete your profile for a faster checkout experience.
                  </p>
                </div>
              </div>

              {/* Stats */}
              <div className="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <StatCard label="Total Orders" value={stats.total} icon={Package} color="bg-primary-900" />
                <StatCard
                  label="Pending Payment"
                  value={stats.pendingPayment}
                  icon={CreditCard}
                  color="bg-warning-500"
                />
                <StatCard label="Processing" value={stats.processing} icon={Truck} color="bg-info-500" />
                <StatCard label="Completed" value={stats.completed} icon={CheckCircle2} color="bg-secondary-500" />
                <StatCard label="Cancelled" value={stats.cancelled} icon={AlertCircle} color="bg-neutral-500" />
              </div>

              {/* Quick Actions */}
              <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6">
                <h2 className="text-lg font-bold text-primary-900 mb-4">Quick Actions</h2>
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-3">
                  {quickActions.map((action) => (
                    <Link
                      key={action.label}
                      href={action.href}
                      className={`flex flex-col items-center gap-2 p-4 rounded-xl ${action.color} text-white hover:opacity-90 transition-opacity-base`}
                    >
                      <action.icon className="w-6 h-6" />
                      <span className="text-sm font-semibold text-center">{action.label}</span>
                    </Link>
                  ))}
                </div>
              </div>

              <div className="grid lg:grid-cols-2 gap-6">
                {/* Recent Orders */}
                <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6">
                  <div className="flex items-center justify-between mb-4">
                    <h2 className="text-lg font-bold text-primary-900">Recent Orders</h2>
                    <Link href="/account/orders" className="text-sm font-semibold text-secondary-600 hover:text-secondary-600">
                      View All
                    </Link>
                  </div>

                  {ordersLoading ? (
                    <div className="py-8 text-center">
                      <Loader2 className="w-6 h-6 animate-spin text-secondary-500 mx-auto" />
                    </div>
                  ) : recentOrders.length === 0 ? (
                    <div className="py-8 text-center text-muted">
                      <Package className="w-10 h-10 mx-auto mb-2 text-placeholder" />
                      <p>No orders yet.</p>
                      <Link href="/products" className="text-secondary-600 font-semibold hover:text-secondary-600">
                        Start Shopping
                      </Link>
                    </div>
                  ) : (
                    <div className="space-y-3">
                      {recentOrders.map((order) => (
                        <OrderRow key={order.id} order={order} />
                      ))}
                    </div>
                  )}
                </div>

                {/* Saved Addresses Preview */}
                <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6">
                  <div className="flex items-center justify-between mb-4">
                    <h2 className="text-lg font-bold text-primary-900">Saved Addresses</h2>
                    <Link href="/account/addresses" className="text-sm font-semibold text-secondary-600 hover:text-secondary-600">
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
                      <Link href="/account/addresses" className="text-secondary-600 font-semibold hover:text-secondary-600">
                        Add Address
                      </Link>
                    </div>
                  ) : (
                    <div className="space-y-3">
                      {savedAddresses.map((addr: Address) => (
                        <div key={addr.id} className="p-4 rounded-xl bg-surface-page">
                          <div className="flex items-center gap-2 mb-1">
                            <Home className="w-4 h-4 text-secondary-600" />
                            <span className="font-semibold text-primary-900">{addr.label}</span>
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

              {/* Recent Activity Preview */}
              <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6">
                <div className="flex items-center justify-between mb-4">
                  <h2 className="text-lg font-bold text-primary-900">Recent Activity</h2>
                  <Link href="/account/activity" className="text-sm font-semibold text-secondary-600 hover:text-secondary-600">
                    View All
                  </Link>
                </div>

                {activityLoading ? (
                  <div className="py-8 text-center">
                    <Loader2 className="w-6 h-6 animate-spin text-secondary-500 mx-auto" />
                  </div>
                ) : recentActivity.length === 0 ? (
                  <div className="py-8 text-center text-muted">
                    <Activity className="w-10 h-10 mx-auto mb-2 text-placeholder" />
                    <p>No recent activity.</p>
                  </div>
                ) : (
                  <div className="space-y-3">
                    {recentActivity.map((item) => (
                      <div key={item.id} className="flex items-start gap-3 p-4 rounded-xl bg-surface-page">
                        <Activity className="w-4 h-4 text-secondary-600 mt-0.5" />
                        <div>
                          <p className="text-sm font-medium text-primary-900">{item.description}</p>
                          <p className="text-xs text-muted">{new Date(item.created_at).toLocaleString()}</p>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>

              {/* Feedback */}
              <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6">
                <h2 className="text-lg font-bold text-primary-900 mb-4">Send Feedback</h2>
                <p className="text-sm text-muted mb-4">
                  We value your opinion. Let us know how we can improve your experience.
                </p>
                <FeedbackForm
                  onSubmit={async (data) => {
                    await submitFeedback(data);
                  }}
                />
              </div>
            </div>
          </div>
        </Container>
      </section>
    </>
  );
}
