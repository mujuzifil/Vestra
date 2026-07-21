"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import Image from "next/image";
import Link from "next/link";
import { Loader2, Check, CreditCard, Truck, MapPin, Package, ChevronRight, Smartphone } from "lucide-react";
import { Container } from "@/components/common/container";
import { useAuth } from "@/lib/auth-context";
import { useCartContext } from "@/lib/cart-context";
import { useAddresses } from "@/hooks/use-addresses";
import { useCheckout } from "@/hooks/use-orders";
import { cn } from "@/lib/utils";

const paymentMethods = [
  { id: "cod", label: "Cash on Delivery", icon: Truck, description: "Pay when your order arrives" },
  { id: "mtn_momo", label: "MTN Mobile Money", icon: Smartphone, description: "Pay via MTN Mobile Money" },
  { id: "airtel_money", label: "Airtel Money", icon: Smartphone, description: "Pay via Airtel Money" },
  { id: "card", label: "Card Payment", icon: CreditCard, description: "Visa / Mastercard" },
];

export function CheckoutPageClient() {
  const router = useRouter();
  const { isAuthenticated } = useAuth();
  const { cart } = useCartContext();
  const { data: addresses } = useAddresses();
  const checkoutMutation = useCheckout();

  const [step, setStep] = useState<"shipping" | "payment" | "review">("shipping");
  const [selectedAddressId, setSelectedAddressId] = useState<number | null>(null);
  const [selectedPayment, setSelectedPayment] = useState("cod");
  const [error, setError] = useState("");

  useEffect(() => {
    if (!isAuthenticated) {
      router.push("/auth/login");
    }
  }, [isAuthenticated, router]);

  useEffect(() => {
    if (addresses && addresses.length > 0) {
      const defaultAddr = addresses.find((a) => a.is_default);
      setSelectedAddressId(defaultAddr?.id || addresses[0].id);
    }
  }, [addresses]);

  const selectedAddress = addresses?.find((a) => a.id === selectedAddressId);

  const isDigitalPayment = selectedPayment !== "cod";

  const handlePlaceOrder = async () => {
    if (!selectedAddress) {
      setError("Please select a shipping address.");
      return;
    }

    setError("");
    try {
      const order = await checkoutMutation.mutateAsync({
        payment_method: selectedPayment,
        shipping_address: {
          full_name: selectedAddress.full_name,
          phone: selectedAddress.phone,
          city: selectedAddress.city,
          region: selectedAddress.region || "",
          district: selectedAddress.district || "",
          address_line: selectedAddress.address_line,
        },
      });

      // If digital payment, redirect to Flutterwave
      if (isDigitalPayment && order.payment_url) {
        window.location.href = order.payment_url;
        return;
      }

      router.push("/checkout/confirm");
    } catch (err) {
      setError(err instanceof Error ? err.message : "Failed to place order.");
    }
  };

  if (!isAuthenticated) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-green-500" />
      </div>
    );
  }

  if (!cart || cart.items.length === 0) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Container className="text-center">
          <Package className="w-12 h-12 text-[#94a3b8] mx-auto mb-4" />
          <h2 className="text-xl font-bold text-[#0a1628] mb-2">Your cart is empty</h2>
          <p className="text-[#64748b] mb-4">Add some products before checking out.</p>
          <Link href="/products" className="inline-flex items-center gap-2 px-6 py-2.5 rounded-full font-semibold text-sm bg-green-500 text-white hover:bg-green-600 transition-colors">
            Browse Products
          </Link>
        </Container>
      </div>
    );
  }

  return (
    <div className="py-12 lg:py-20 bg-[#f8fafc]">
      <Container>
        <h1 className="text-2xl lg:text-3xl font-extrabold text-[#0a1628] mb-8">Checkout</h1>

        {/* Progress */}
        <div className="flex items-center gap-2 mb-8">
          {(["shipping", "payment", "review"] as const).map((s, i) => (
            <div key={s} className="flex items-center gap-2">
              <div
                className={cn(
                  "w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold",
                  step === s || (["payment", "review"].includes(step) && s === "shipping") || (step === "review" && s === "payment")
                    ? "bg-green-500 text-white"
                    : "bg-[#e2e8f0] text-[#64748b]"
                )}
              >
                {(["payment", "review"].includes(step) && s === "shipping") || (step === "review" && s === "payment") ? (
                  <Check className="w-4 h-4" />
                ) : (
                  i + 1
                )}
              </div>
              <span className={cn("text-sm font-medium capitalize", step === s ? "text-[#0a1628]" : "text-[#94a3b8]")}>
                {s}
              </span>
              {i < 2 && <ChevronRight className="w-4 h-4 text-[#94a3b8]" />}
            </div>
          ))}
        </div>

        <div className="grid lg:grid-cols-3 gap-8">
          {/* Main Content */}
          <div className="lg:col-span-2 space-y-6">
            {error && (
              <div className="p-4 rounded-xl bg-red-50 text-red-600 text-sm">{error}</div>
            )}

            {/* Shipping Step */}
            {step === "shipping" && (
              <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
                <h2 className="text-lg font-bold text-[#0a1628] mb-4 flex items-center gap-2">
                  <MapPin className="w-5 h-5 text-green-500" />
                  Shipping Address
                </h2>

                {addresses && addresses.length > 0 ? (
                  <div className="space-y-3">
                    {addresses.map((addr) => (
                      <label
                        key={addr.id}
                        className={cn(
                          "flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all",
                          selectedAddressId === addr.id
                            ? "border-green-500 bg-green-50/50"
                            : "border-[#e2e8f0] hover:border-[#94a3b8]"
                        )}
                      >
                        <input
                          type="radio"
                          name="address"
                          value={addr.id}
                          checked={selectedAddressId === addr.id}
                          onChange={() => setSelectedAddressId(addr.id)}
                          className="mt-1"
                        />
                        <div>
                          <p className="font-semibold text-[#0a1628]">
                            {addr.label} {addr.is_default && <span className="text-xs text-green-600">(Default)</span>}
                          </p>
                          <p className="text-sm text-[#64748b]">{addr.full_name}, {addr.phone}</p>
                          <p className="text-sm text-[#64748b]">{addr.address_line}, {addr.city}</p>
                        </div>
                      </label>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-8">
                    <p className="text-[#64748b] mb-4">No addresses saved yet.</p>
                    <Link
                      href="/account/addresses"
                      className="inline-flex items-center gap-2 px-5 py-2.5 rounded-full font-semibold text-sm bg-green-500 text-white hover:bg-green-600 transition-colors"
                    >
                      Add Address
                    </Link>
                  </div>
                )}

                <button
                  onClick={() => setStep("payment")}
                  disabled={!selectedAddressId}
                  className={cn(
                    "mt-6 w-full inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-1 transition-all",
                    !selectedAddressId && "opacity-50 cursor-not-allowed"
                  )}
                >
                  Continue to Payment
                  <ChevronRight className="w-4 h-4" />
                </button>
              </div>
            )}

            {/* Payment Step */}
            {step === "payment" && (
              <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
                <h2 className="text-lg font-bold text-[#0a1628] mb-4 flex items-center gap-2">
                  <CreditCard className="w-5 h-5 text-green-500" />
                  Payment Method
                </h2>

                <div className="space-y-3">
                  {paymentMethods.map((method) => (
                    <label
                      key={method.id}
                      className={cn(
                        "flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all",
                        selectedPayment === method.id
                          ? "border-green-500 bg-green-50/50"
                          : "border-[#e2e8f0] hover:border-[#94a3b8]"
                      )}
                    >
                      <input
                        type="radio"
                        name="payment"
                        value={method.id}
                        checked={selectedPayment === method.id}
                        onChange={() => setSelectedPayment(method.id)}
                        className="mt-1"
                      />
                      <div className="flex-1">
                        <div className="flex items-center gap-2">
                          <method.icon className="w-5 h-5 text-[#0a1628]" />
                          <span className="font-semibold text-[#0a1628]">{method.label}</span>
                          {method.id === "cod" && (
                            <span className="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">Popular</span>
                          )}
                        </div>
                        <p className="text-sm text-[#64748b] mt-0.5">{method.description}</p>
                      </div>
                    </label>
                  ))}
                </div>

                <div className="flex gap-3 mt-6">
                  <button
                    onClick={() => setStep("shipping")}
                    className="px-6 py-3 rounded-full font-semibold text-sm text-[#64748b] border border-[#e2e8f0] hover:bg-[#f8fafc] transition-colors"
                  >
                    Back
                  </button>
                  <button
                    onClick={() => setStep("review")}
                    className="flex-1 inline-flex items-center justify-center gap-2 px-7 py-3 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-1 transition-all"
                  >
                    Review Order
                    <ChevronRight className="w-4 h-4" />
                  </button>
                </div>
              </div>
            )}

            {/* Review Step */}
            {step === "review" && (
              <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
                <h2 className="text-lg font-bold text-[#0a1628] mb-4">Review Your Order</h2>

                <div className="space-y-4 mb-6">
                  <div className="p-4 rounded-xl bg-[#f8fafc]">
                    <p className="text-sm font-semibold text-[#0a1628] mb-1">Shipping To</p>
                    <p className="text-sm text-[#64748b]">
                      {selectedAddress?.full_name}, {selectedAddress?.phone}
                    </p>
                    <p className="text-sm text-[#64748b]">
                      {selectedAddress?.address_line}, {selectedAddress?.city}
                    </p>
                  </div>
                  <div className="p-4 rounded-xl bg-[#f8fafc]">
                    <p className="text-sm font-semibold text-[#0a1628] mb-1">Payment Method</p>
                    <p className="text-sm text-[#64748b]">
                      {paymentMethods.find((m) => m.id === selectedPayment)?.label}
                    </p>
                    {isDigitalPayment && (
                      <p className="text-xs text-amber-600 mt-1">
                        You will be redirected to complete payment after placing the order.
                      </p>
                    )}
                  </div>
                </div>

                <div className="flex gap-3">
                  <button
                    onClick={() => setStep("payment")}
                    className="px-6 py-3 rounded-full font-semibold text-sm text-[#64748b] border border-[#e2e8f0] hover:bg-[#f8fafc] transition-colors"
                  >
                    Back
                  </button>
                  <button
                    onClick={handlePlaceOrder}
                    disabled={checkoutMutation.isPending}
                    className={cn(
                      "flex-1 inline-flex items-center justify-center gap-2 px-7 py-3 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-1 transition-all",
                      checkoutMutation.isPending && "opacity-70 cursor-not-allowed"
                    )}
                  >
                    {checkoutMutation.isPending ? (
                      <Loader2 className="w-4 h-4 animate-spin" />
                    ) : (
                      <Check className="w-4 h-4" />
                    )}
                    {checkoutMutation.isPending
                      ? "Placing Order..."
                      : isDigitalPayment
                      ? "Place Order & Pay"
                      : "Place Order"}
                  </button>
                </div>
              </div>
            )}
          </div>

          {/* Order Summary */}
          <div className="lg:col-span-1">
            <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 sticky top-24">
              <h2 className="text-lg font-bold text-[#0a1628] mb-4">Order Summary</h2>
              <div className="space-y-3 mb-4">
                {cart.items.map((item) => (
                  <div key={item.id} className="flex gap-3">
                    <div className="relative w-14 h-14 rounded-lg bg-[#f8fafc] overflow-hidden flex-shrink-0">
                      <Image
                        src={item.product?.images?.[0]?.image || "/assets/images/products/placeholder.png"}
                        alt={item.product?.name || ""}
                        fill
                        className="object-contain p-1"
                      />
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium text-[#0a1628] truncate">{item.product?.name}</p>
                      <p className="text-xs text-[#64748b]">Qty: {item.quantity}</p>
                    </div>
                    <p className="text-sm font-semibold text-[#0a1628]">UGX {item.line_total}</p>
                  </div>
                ))}
              </div>
              <div className="border-t border-[#e2e8f0] pt-4 space-y-2">
                <div className="flex justify-between text-sm">
                  <span className="text-[#64748b]">Subtotal</span>
                  <span className="font-medium">UGX {cart.subtotal}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-[#64748b]">Shipping</span>
                  <span className="font-medium">Free</span>
                </div>
                <div className="flex justify-between text-lg font-bold pt-2 border-t border-[#e2e8f0]">
                  <span>Total</span>
                  <span>UGX {cart.subtotal}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Container>
    </div>
  );
}
