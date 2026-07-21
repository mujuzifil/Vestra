"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { User, Package, MapPin, LogOut, ChevronRight, Loader2, MessageSquare } from "lucide-react";
import { FeedbackForm } from "@/components/feedback/feedback-form";
import { submitFeedback } from "@/lib/api/feedback";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useOrders } from "@/hooks/use-orders";

export function AccountPageClient() {
  const router = useRouter();
  const { user, isAuthenticated, isLoading: authLoading, logout } = useAuth();
  const { data: orders, isLoading: ordersLoading } = useOrders();

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  if (authLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-green-500" />
      </div>
    );
  }

  if (!isAuthenticated || !user) {
    return null;
  }

  const recentOrders = orders?.slice(0, 3) || [];

  const menuItems = [
    { icon: Package, label: "My Orders", href: "/account/orders", description: "View and track your orders" },
    { icon: MapPin, label: "Addresses", href: "/account/addresses", description: "Manage delivery addresses" },
    { icon: User, label: "Settings", href: "/account/settings", description: "Update profile and password" },
  ];

  return (
    <>
      <PageHero title="My Account" subtitle={`Welcome back, ${user.name}`} breadcrumb={[{ label: "Account" }]} />

      <section className="py-12 lg:py-20 bg-[#f8fafc]">
        <Container>
          <div className="grid lg:grid-cols-3 gap-8">
            {/* Sidebar */}
            <div className="lg:col-span-1">
              <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
                <div className="flex items-center gap-4 mb-6 pb-6 border-b border-[#e2e8f0]">
                  <div className="w-12 h-12 rounded-full bg-green-500/10 text-green-600 flex items-center justify-center">
                    <User className="w-6 h-6" />
                  </div>
                  <div>
                    <p className="font-bold text-[#0a1628]">{user.name}</p>
                    <p className="text-sm text-[#64748b]">{user.email}</p>
                  </div>
                </div>

                <nav className="space-y-2">
                  {menuItems.map((item) => (
                    <Link
                      key={item.href}
                      href={item.href}
                      className="flex items-center gap-3 p-3 rounded-xl text-[#475569] hover:bg-[#f8fafc] hover:text-[#0a1628] transition-colors"
                    >
                      <item.icon className="w-5 h-5" />
                      <span className="font-medium">{item.label}</span>
                      <ChevronRight className="w-4 h-4 ml-auto" />
                    </Link>
                  ))}
                  <button
                    onClick={() => logout().then(() => router.push("/"))}
                    className="w-full flex items-center gap-3 p-3 rounded-xl text-red-600 hover:bg-red-50 transition-colors"
                  >
                    <LogOut className="w-5 h-5" />
                    <span className="font-medium">Sign Out</span>
                  </button>
                </nav>
              </div>
            </div>

            {/* Main Content */}
            <div className="lg:col-span-2 space-y-8">
              {/* Account Summary */}
              <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
                <h2 className="text-lg font-bold text-[#0a1628] mb-4">Account Summary</h2>
                <div className="grid sm:grid-cols-2 gap-4">
                  <div className="p-4 rounded-xl bg-[#f8fafc]">
                    <p className="text-sm text-[#64748b]">Total Orders</p>
                    <p className="text-2xl font-extrabold text-[#0a1628]">{orders?.length || 0}</p>
                  </div>
                  <div className="p-4 rounded-xl bg-[#f8fafc]">
                    <p className="text-sm text-[#64748b]">Member Since</p>
                    <p className="text-2xl font-extrabold text-[#0a1628]">
                      {new Date(user.created_at).toLocaleDateString()}
                    </p>
                  </div>
                </div>
              </div>

              {/* Recent Orders */}
              <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
                <div className="flex items-center justify-between mb-4">
                  <h2 className="text-lg font-bold text-[#0a1628]">Recent Orders</h2>
                  <Link href="/account/orders" className="text-sm font-semibold text-green-600 hover:text-green-700">
                    View All
                  </Link>
                </div>

                {ordersLoading ? (
                  <div className="py-8 text-center">
                    <Loader2 className="w-6 h-6 animate-spin text-green-500 mx-auto" />
                  </div>
                ) : recentOrders.length === 0 ? (
                  <div className="py-8 text-center text-[#64748b]">
                    <Package className="w-10 h-10 mx-auto mb-2 text-[#94a3b8]" />
                    <p>No orders yet.</p>
                    <Link href="/products" className="text-green-600 font-semibold hover:text-green-700">
                      Start Shopping
                    </Link>
                  </div>
                ) : (
                  <div className="space-y-3">
                    {recentOrders.map((order) => (
                      <div key={order.id} className="flex items-center justify-between p-4 rounded-xl bg-[#f8fafc]">
                        <div>
                          <p className="font-semibold text-[#0a1628]">{order.invoice_number}</p>
                          <p className="text-sm text-[#64748b]">{new Date(order.created_at).toLocaleDateString()}</p>
                        </div>
                        <div className="text-right">
                          <p className="font-bold text-[#0d3b66]">UGX {order.total_amount}</p>
                          <span className="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 capitalize">
                            {order.status}
                          </span>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
              {/* Feedback */}
              <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
                <h2 className="text-lg font-bold text-[#0a1628] mb-4">Send Feedback</h2>
                <p className="text-sm text-[#64748b] mb-4">
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
