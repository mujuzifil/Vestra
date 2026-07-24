"use client";

import { useState, useEffect } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import Image from "next/image";
import Link from "next/link";
import {
  Loader2,
  Check,
  CreditCard,
  Truck,
  MapPin,
  Package,
  ChevronRight,
  Smartphone,
  User,
  AlertCircle,
  Store,
  Clock,
  Home,
  ShieldCheck,
} from "lucide-react";
import { Container } from "@/components/common/container";
import { InputField } from "@/components/common/form-field";
import { useAuth } from "@/lib/auth-context";
import { useCartContext } from "@/lib/cart-context";
import { useAddresses } from "@/hooks/use-addresses";
import { useCheckout } from "@/hooks/use-orders";
import { formatPrice, cn } from "@/lib/utils";
import { toastError, toastSuccess } from "@/lib/toast-utils";

const paymentMethods = [
  { id: "cod", label: "Cash on Delivery", icon: Truck, description: "Pay when your order arrives" },
  { id: "mtn_momo", label: "MTN Mobile Money", icon: Smartphone, description: "Pay via MTN Mobile Money" },
  { id: "airtel_money", label: "Airtel Money", icon: Smartphone, description: "Pay via Airtel Money" },
  { id: "card", label: "Card Payment", icon: CreditCard, description: "Visa / Mastercard" },
];

const shippingMethods = [
  {
    id: "standard",
    label: "Standard Delivery",
    icon: Truck,
    description: "3–5 business days",
    cost: 0,
  },
  {
    id: "express",
    label: "Express Delivery",
    icon: Clock,
    description: "1–2 business days",
    cost: 15000,
  },
  {
    id: "pickup",
    label: "Pickup",
    icon: Store,
    description: "Collect from our store (coming soon)",
    cost: 0,
  },
];

const TAX_RATE = 0.18;
const GUEST_INFO_KEY = "vestra_guest_checkout_info";

interface CustomerInfo {
  name: string;
  email: string;
  phone: string;
}

interface ShippingAddress {
  full_name: string;
  phone: string;
  email: string;
  country: string;
  region: string;
  district: string;
  city: string;
  address_line: string;
  postal_code: string;
  landmark: string;
  instructions: string;
}

interface FormErrors {
  customer?: Partial<Record<keyof CustomerInfo, string>>;
  shipping?: Partial<Record<keyof ShippingAddress, string>>;
  general?: string;
}

function generateGuestPassword(): string {
  return `Guest-${crypto.randomUUID()}-${Date.now()}`;
}

function loadGuestInfo(): CustomerInfo & ShippingAddress {
  if (typeof window === "undefined") {
    return {
      name: "",
      email: "",
      phone: "",
      full_name: "",
      country: "Uganda",
      region: "",
      district: "",
      city: "",
      address_line: "",
      postal_code: "",
      landmark: "",
      instructions: "",
    } as CustomerInfo & ShippingAddress;
  }
  try {
    const raw = localStorage.getItem(GUEST_INFO_KEY);
    if (raw) {
      return { ...JSON.parse(raw), country: "Uganda" };
    }
  } catch {
    // ignore
  }
  return {
    name: "",
    email: "",
    phone: "",
    full_name: "",
    country: "Uganda",
    region: "",
    district: "",
    city: "",
    address_line: "",
    postal_code: "",
    landmark: "",
    instructions: "",
  } as CustomerInfo & ShippingAddress;
}

function saveGuestInfo(info: CustomerInfo & ShippingAddress) {
  if (typeof window === "undefined") return;
  try {
    localStorage.setItem(GUEST_INFO_KEY, JSON.stringify(info));
  } catch {
    // ignore
  }
}

export function CheckoutPageClient() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const returnUrl = searchParams.get("returnUrl") || "/checkout";
  const { user, isAuthenticated, isLoading: authLoading, register } = useAuth();
  const { cart, mergeGuestCart } = useCartContext();
  const { data: addresses, create: createAddress } = useAddresses();
  const checkoutMutation = useCheckout();

  const [step, setStep] = useState<"customer" | "shipping" | "payment" | "review">("customer");
  const [customer, setCustomer] = useState<CustomerInfo>({ name: "", email: "", phone: "" });
  const [shipping, setShipping] = useState<ShippingAddress>({
    full_name: "",
    phone: "",
    email: "",
    country: "Uganda",
    region: "",
    district: "",
    city: "",
    address_line: "",
    postal_code: "",
    landmark: "",
    instructions: "",
  });
  const [selectedAddressId, setSelectedAddressId] = useState<number | null>(null);
  const [shippingMethod, setShippingMethod] = useState("standard");
  const [selectedPayment, setSelectedPayment] = useState("cod");
  const [errors, setErrors] = useState<FormErrors>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [saveNewAddress, setSaveNewAddress] = useState(false);

  // Initialize authenticated user data
  useEffect(() => {
    if (isAuthenticated && user) {
      setCustomer({
        name: user.name || "",
        email: user.email || "",
        phone: user.phone || "",
      });
    }
  }, [isAuthenticated, user]);

  // Initialize saved addresses
  useEffect(() => {
    if (addresses && addresses.length > 0) {
      const defaultAddr =
        addresses.find((a) => a.is_default_shipping) ||
        addresses.find((a) => a.is_default) ||
        addresses[0];
      setSelectedAddressId(defaultAddr.id);
      if (isAuthenticated) {
        setShipping({
          full_name: defaultAddr.full_name,
          phone: defaultAddr.phone,
          email: user?.email || "",
          country: defaultAddr.country || "Uganda",
          region: defaultAddr.region || "",
          district: defaultAddr.district || "",
          city: defaultAddr.city,
          address_line: defaultAddr.address_line,
          postal_code: defaultAddr.postal_code || "",
          landmark: "",
          instructions: defaultAddr.delivery_notes || "",
        });
      }
    }
  }, [addresses, isAuthenticated, user]);

  // Load guest info for guests
  useEffect(() => {
    if (!isAuthenticated && !authLoading) {
      const saved = loadGuestInfo();
      setCustomer({
        name: saved.name || "",
        email: saved.email || "",
        phone: saved.phone || "",
      });
      setShipping({
        full_name: saved.full_name || "",
        phone: saved.phone || "",
        email: saved.email || "",
        country: saved.country || "Uganda",
        region: saved.region || "",
        district: saved.district || "",
        city: saved.city || "",
        address_line: saved.address_line || "",
        postal_code: saved.postal_code || "",
        landmark: saved.landmark || "",
        instructions: saved.instructions || "",
      });
    }
  }, [isAuthenticated, authLoading]);

  // Persist guest info
  useEffect(() => {
    if (!isAuthenticated) {
      saveGuestInfo({ ...customer, ...shipping });
    }
  }, [customer, shipping, isAuthenticated]);

  if (authLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-green-500" />
      </div>
    );
  }

  if (!cart || cart.items.length === 0) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center bg-[#f8fafc]">
        <Container className="text-center">
          <Package className="w-12 h-12 text-[#94a3b8] mx-auto mb-4" />
          <h2 className="text-xl font-bold text-[#0a1628] mb-2">Your cart is empty</h2>
          <p className="text-[#64748b] mb-4">Add some products before checking out.</p>
          <Link
            href="/products"
            className="inline-flex items-center gap-2 px-6 py-2.5 rounded-full font-semibold text-sm bg-green-500 text-white hover:bg-green-600 transition-colors"
          >
            Browse Products
          </Link>
        </Container>
      </div>
    );
  }

  const subtotal = Number(cart.subtotal || 0);
  const shippingCost = shippingMethod === "express" ? 15000 : 0;
  const taxAmount = Math.round(subtotal * TAX_RATE * 100) / 100;
  const totalAmount = subtotal + shippingCost + taxAmount;
  const selectedShipping = shippingMethods.find((m) => m.id === shippingMethod);
  const selectedPaymentMethod = paymentMethods.find((m) => m.id === selectedPayment);

  const validateCustomer = (): boolean => {
    const next: FormErrors["customer"] = {};
    if (!customer.name || customer.name.length < 2) next.name = "Full name is required.";
    if (!customer.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(customer.email)) {
      next.email = "Please enter a valid email address.";
    }
    if (!customer.phone || customer.phone.length < 7) next.phone = "Please enter a valid phone number.";

    setErrors((prev) => ({ ...prev, customer: next }));
    return Object.keys(next).length === 0;
  };

  const validateShipping = (): boolean => {
    const next: FormErrors["shipping"] = {};
    if (!shipping.full_name || shipping.full_name.length < 2) next.full_name = "Recipient name is required.";
    if (!shipping.phone || shipping.phone.length < 7) next.phone = "Phone number is required.";
    if (!shipping.city || shipping.city.length < 2) next.city = "City is required.";
    if (!shipping.address_line || shipping.address_line.length < 5) {
      next.address_line = "Street address is required (at least 5 characters).";
    }

    setErrors((prev) => ({ ...prev, shipping: next }));
    return Object.keys(next).length === 0;
  };

  const handleNext = () => {
    setErrors({});
    if (step === "customer") {
      if (validateCustomer()) setStep("shipping");
    } else if (step === "shipping") {
      if (validateShipping()) setStep("payment");
    } else if (step === "payment") {
      setStep("review");
    }
  };

  const handlePlaceOrder = async () => {
    setErrors({});
    setIsSubmitting(true);

    try {
      let isLoggedIn = isAuthenticated;

      // Guest checkout: create account, merge cart
      if (!isAuthenticated) {
        const password = generateGuestPassword();
        try {
          await register(customer.name, customer.email, password, customer.phone);
          await mergeGuestCart();
          isLoggedIn = true;
          toastSuccess("Account created. Your order is being placed.");
        } catch (error) {
          const message = error instanceof Error ? error.message : "";
          if (message.toLowerCase().includes("taken") || message.toLowerCase().includes("already") || message.toLowerCase().includes("exists")) {
            setErrors({
              general: "This email is already registered. Please log in or use a different email.",
            });
          } else {
            setErrors({ general: message || "Could not create your account. Please try again." });
          }
          setIsSubmitting(false);
          return;
        }
      }

      if (!isLoggedIn) {
        setErrors({ general: "You must be logged in to place an order." });
        setIsSubmitting(false);
        return;
      }

      // Save new address for authenticated users if requested
      if (isAuthenticated && saveNewAddress && selectedAddressId === null) {
        try {
          await createAddress({
            label: "Shipping",
            full_name: shipping.full_name,
            phone: shipping.phone,
            city: shipping.city,
            region: shipping.region || null,
            district: shipping.district || null,
            address_line: shipping.address_line,
            address_line_2: null,
            postal_code: shipping.postal_code || null,
            country: shipping.country || "Uganda",
            delivery_notes: shipping.instructions || null,
            is_default: false,
            is_default_shipping: false,
            is_default_billing: false,
          });
        } catch {
          // Non-blocking: continue with order even if address save fails
        }
      }

      const order = await checkoutMutation.mutateAsync({
        payment_method: selectedPayment,
        shipping_address: {
          full_name: shipping.full_name,
          phone: shipping.phone,
          city: shipping.city,
          region: shipping.region || "",
          district: shipping.district || "",
          address_line: shipping.address_line,
        },
        shipping_cost: shippingCost,
        tax_amount: taxAmount,
        notes: shipping.instructions || undefined,
      });

      // Clear persisted guest info after successful order
      if (typeof window !== "undefined") {
        localStorage.removeItem(GUEST_INFO_KEY);
      }

      router.push(`/checkout/confirm?order=${order.id}`);
    } catch (error) {
      const message = error instanceof Error ? error.message : "Failed to place order.";
      toastError(message);
      setErrors({ general: message });
    } finally {
      setIsSubmitting(false);
    }
  };

  const steps = [
    { key: "customer", label: "Customer" },
    { key: "shipping", label: "Shipping" },
    { key: "payment", label: "Payment" },
    { key: "review", label: "Review" },
  ] as const;

  return (
    <div className="py-12 lg:py-20 bg-[#f8fafc]">
      <Container>
        <h1 className="text-2xl lg:text-3xl font-extrabold text-[#0a1628] mb-6">Checkout</h1>

        {/* Stepper */}
        <div className="flex items-center gap-2 mb-8 overflow-x-auto pb-2">
          {steps.map((s, i) => {
            const active = step === s.key;
            const completed =
              steps.findIndex((x) => x.key === step) > i;
            return (
              <div key={s.key} className="flex items-center gap-2 flex-shrink-0">
                <div
                  className={cn(
                    "w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors",
                    active ? "bg-green-500 text-white" : completed ? "bg-green-500 text-white" : "bg-[#e2e8f0] text-[#64748b]"
                  )}
                >
                  {completed ? <Check className="w-4 h-4" /> : i + 1}
                </div>
                <span className={cn("text-sm font-medium whitespace-nowrap", active ? "text-[#0a1628]" : completed ? "text-[#0a1628]" : "text-[#94a3b8]")}>
                  {s.label}
                </span>
                {i < steps.length - 1 && <ChevronRight className="w-4 h-4 text-[#94a3b8]" />}
              </div>
            );
          })}
        </div>

        {errors.general && (
          <div className="mb-6 p-4 rounded-xl bg-red-50 text-red-600 text-sm flex items-start gap-3">
            <AlertCircle className="w-5 h-5 flex-shrink-0 mt-0.5" />
            <div>
              <p className="font-semibold">Unable to complete checkout</p>
              <p>{errors.general}</p>
              {errors.general.includes("already registered") && (
                <Link href={`/auth/login?returnUrl=${encodeURIComponent(returnUrl)}`} className="font-semibold underline mt-1 inline-block">
                  Log in here
                </Link>
              )}
            </div>
          </div>
        )}

        <div className="grid lg:grid-cols-3 gap-8">
          {/* Main Content */}
          <div className="lg:col-span-2 space-y-6">
            {/* Customer Step */}
            {step === "customer" && (
              <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
                <h2 className="text-lg font-bold text-[#0a1628] mb-4 flex items-center gap-2">
                  <User className="w-5 h-5 text-green-500" />
                  Customer Information
                </h2>

                {isAuthenticated ? (
                  <div className="p-4 rounded-xl bg-[#f8fafc] border border-[#e2e8f0]">
                    <p className="font-semibold text-[#0a1628]">{customer.name}</p>
                    <p className="text-sm text-[#64748b]">{customer.email}</p>
                    <p className="text-sm text-[#64748b]">{customer.phone}</p>
                  </div>
                ) : (
                  <div className="space-y-4">
                    <InputField
                      id="name"
                      name="name"
                      label="Full Name"
                      value={customer.name}
                      onChange={(e) => setCustomer((c) => ({ ...c, name: e.target.value }))}
                      error={errors.customer?.name}
                      placeholder="John Doe"
                    />
                    <div className="grid sm:grid-cols-2 gap-4">
                      <InputField
                        id="email"
                        name="email"
                        type="email"
                        label="Email Address"
                        value={customer.email}
                        onChange={(e) => setCustomer((c) => ({ ...c, email: e.target.value }))}
                        error={errors.customer?.email}
                        placeholder="john@example.com"
                      />
                      <InputField
                        id="phone"
                        name="phone"
                        type="tel"
                        label="Phone Number"
                        value={customer.phone}
                        onChange={(e) => setCustomer((c) => ({ ...c, phone: e.target.value }))}
                        error={errors.customer?.phone}
                        placeholder="+256 707 128 442"
                      />
                    </div>
                    <p className="text-xs text-[#94a3b8]">
                      A temporary account will be created so you can track your order. You can set a password later.
                    </p>
                  </div>
                )}

                <div className="mt-6 flex justify-end">
                  <button
                    onClick={handleNext}
                    className="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-1 transition-all"
                  >
                    Continue to Shipping
                    <ChevronRight className="w-4 h-4" />
                  </button>
                </div>
              </div>
            )}

            {/* Shipping Step */}
            {step === "shipping" && (
              <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
                <h2 className="text-lg font-bold text-[#0a1628] mb-4 flex items-center gap-2">
                  <MapPin className="w-5 h-5 text-green-500" />
                  Delivery Address
                </h2>

                {isAuthenticated && addresses && addresses.length > 0 && (
                  <div className="space-y-3 mb-6">
                    <p className="text-sm font-semibold text-[#0a1628]">Saved Addresses</p>
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
                          name="savedAddress"
                          value={addr.id}
                          checked={selectedAddressId === addr.id}
                          onChange={() => {
                            setSelectedAddressId(addr.id);
                            setShipping({
                              ...shipping,
                              full_name: addr.full_name,
                              phone: addr.phone,
                              city: addr.city,
                              country: addr.country || "Uganda",
                              region: addr.region || "",
                              district: addr.district || "",
                              address_line: addr.address_line,
                              postal_code: addr.postal_code || "",
                              instructions: addr.delivery_notes || "",
                            });
                          }}
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
                )}

                <div className="space-y-4">
                  <div className="grid sm:grid-cols-2 gap-4">
                    <InputField
                      id="full_name"
                      name="full_name"
                      label="Recipient Name"
                      value={shipping.full_name}
                      onChange={(e) => setShipping((s) => ({ ...s, full_name: e.target.value }))}
                      error={errors.shipping?.full_name}
                      placeholder="Recipient name"
                    />
                    <InputField
                      id="shipping_phone"
                      name="shipping_phone"
                      type="tel"
                      label="Recipient Phone"
                      value={shipping.phone}
                      onChange={(e) => setShipping((s) => ({ ...s, phone: e.target.value }))}
                      error={errors.shipping?.phone}
                      placeholder="+256 707 128 442"
                    />
                  </div>
                  <div className="grid sm:grid-cols-2 gap-4">
                    <InputField
                      id="country"
                      name="country"
                      label="Country"
                      value={shipping.country}
                      disabled
                    />
                    <InputField
                      id="city"
                      name="city"
                      label="City / District"
                      value={shipping.city}
                      onChange={(e) => setShipping((s) => ({ ...s, city: e.target.value }))}
                      error={errors.shipping?.city}
                      placeholder="Kampala"
                    />
                  </div>
                  <div className="grid sm:grid-cols-2 gap-4">
                    <InputField
                      id="region"
                      name="region"
                      label="Region / State"
                      value={shipping.region}
                      onChange={(e) => setShipping((s) => ({ ...s, region: e.target.value }))}
                      placeholder="Central"
                    />
                    <InputField
                      id="district"
                      name="district"
                      label="District / County"
                      value={shipping.district}
                      onChange={(e) => setShipping((s) => ({ ...s, district: e.target.value }))}
                      placeholder="Kampala"
                    />
                  </div>
                  <InputField
                    id="address_line"
                    name="address_line"
                    label="Street Address"
                    value={shipping.address_line}
                    onChange={(e) => setShipping((s) => ({ ...s, address_line: e.target.value }))}
                    error={errors.shipping?.address_line}
                    placeholder="Plot 123, Main Street"
                  />
                  <div className="grid sm:grid-cols-2 gap-4">
                    <InputField
                      id="postal_code"
                      name="postal_code"
                      label="Postal Code"
                      value={shipping.postal_code}
                      onChange={(e) => setShipping((s) => ({ ...s, postal_code: e.target.value }))}
                      placeholder="00000"
                    />
                    <InputField
                      id="landmark"
                      name="landmark"
                      label="Nearest Landmark"
                      value={shipping.landmark}
                      onChange={(e) => setShipping((s) => ({ ...s, landmark: e.target.value }))}
                      placeholder="Near Shell petrol station"
                    />
                  </div>
                  <InputField
                    id="instructions"
                    name="instructions"
                    label="Delivery Instructions (optional)"
                    value={shipping.instructions}
                    onChange={(e) => setShipping((s) => ({ ...s, instructions: e.target.value }))}
                    placeholder="Gate code, floor number, etc."
                  />

                  {isAuthenticated && (
                    <label className="flex items-center gap-2 text-sm text-[#475569]">
                      <input
                        type="checkbox"
                        checked={saveNewAddress}
                        onChange={(e) => setSaveNewAddress(e.target.checked)}
                        className="rounded border-[#e2e8f0] text-green-600 focus:ring-green-500"
                      />
                      Save this address for future orders
                    </label>
                  )}
                </div>

                <div className="mt-6 flex justify-between">
                  <button
                    onClick={() => setStep("customer")}
                    className="px-6 py-3 rounded-full font-semibold text-sm text-[#64748b] border border-[#e2e8f0] hover:bg-[#f8fafc] transition-colors"
                  >
                    Back
                  </button>
                  <button
                    onClick={handleNext}
                    className="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-1 transition-all"
                  >
                    Continue to Payment
                    <ChevronRight className="w-4 h-4" />
                  </button>
                </div>
              </div>
            )}

            {/* Payment Step */}
            {step === "payment" && (
              <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
                <h2 className="text-lg font-bold text-[#0a1628] mb-4 flex items-center gap-2">
                  <CreditCard className="w-5 h-5 text-green-500" />
                  Payment Method
                </h2>

                <div className="space-y-3 mb-6">
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

                <h3 className="text-base font-bold text-[#0a1628] mb-3 flex items-center gap-2">
                  <Truck className="w-4 h-4 text-green-500" />
                  Shipping Method
                </h3>
                <div className="space-y-3">
                  {shippingMethods.map((method) => (
                    <label
                      key={method.id}
                      className={cn(
                        "flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all",
                        shippingMethod === method.id
                          ? "border-green-500 bg-green-50/50"
                          : "border-[#e2e8f0] hover:border-[#94a3b8]"
                      )}
                    >
                      <input
                        type="radio"
                        name="shipping"
                        value={method.id}
                        checked={shippingMethod === method.id}
                        onChange={() => setShippingMethod(method.id)}
                        disabled={method.id === "pickup"}
                        className="mt-1"
                      />
                      <div className="flex-1">
                        <div className="flex items-center justify-between">
                          <div className="flex items-center gap-2">
                            <method.icon className="w-5 h-5 text-[#0a1628]" />
                            <span className="font-semibold text-[#0a1628]">{method.label}</span>
                          </div>
                          <span className="text-sm font-semibold text-[#0d3b66]">
                            {method.cost === 0 ? "Free" : formatPrice(method.cost)}
                          </span>
                        </div>
                        <p className="text-sm text-[#64748b] mt-0.5">{method.description}</p>
                      </div>
                    </label>
                  ))}
                </div>

                <div className="flex justify-between mt-6">
                  <button
                    onClick={() => setStep("shipping")}
                    className="px-6 py-3 rounded-full font-semibold text-sm text-[#64748b] border border-[#e2e8f0] hover:bg-[#f8fafc] transition-colors"
                  >
                    Back
                  </button>
                  <button
                    onClick={handleNext}
                    className="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-1 transition-all"
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
                    <p className="text-sm font-semibold text-[#0a1628] mb-1 flex items-center gap-2">
                      <User className="w-4 h-4 text-green-500" /> Customer
                    </p>
                    <p className="text-sm text-[#64748b]">{customer.name}</p>
                    <p className="text-sm text-[#64748b]">{customer.email}</p>
                    <p className="text-sm text-[#64748b]">{customer.phone}</p>
                  </div>
                  <div className="p-4 rounded-xl bg-[#f8fafc]">
                    <p className="text-sm font-semibold text-[#0a1628] mb-1 flex items-center gap-2">
                      <MapPin className="w-4 h-4 text-green-500" /> Shipping To
                    </p>
                    <p className="text-sm text-[#64748b]">{shipping.full_name}, {shipping.phone}</p>
                    <p className="text-sm text-[#64748b]">
                      {shipping.address_line}, {shipping.city}
                    </p>
                    <p className="text-sm text-[#64748b]">
                      {shipping.region} {shipping.district} {shipping.postal_code}
                    </p>
                    {shipping.instructions && (
                      <p className="text-sm text-[#64748b] mt-1">Instructions: {shipping.instructions}</p>
                    )}
                  </div>
                  <div className="p-4 rounded-xl bg-[#f8fafc]">
                    <p className="text-sm font-semibold text-[#0a1628] mb-1 flex items-center gap-2">
                      <CreditCard className="w-4 h-4 text-green-500" /> Payment Method
                    </p>
                    <p className="text-sm text-[#64748b]">{selectedPaymentMethod?.label}</p>
                    <p className="text-sm text-[#64748b]">
                      Shipping: {selectedShipping?.label} ({selectedShipping?.cost === 0 ? "Free" : formatPrice(selectedShipping?.cost || 0)})
                    </p>
                    {selectedPayment !== "cod" && (
                      <p className="text-xs text-amber-600 mt-1">
                        You will be redirected to complete payment after placing the order.
                      </p>
                    )}
                  </div>
                </div>

                <div className="flex gap-3">
                  <button
                    onClick={() => setStep("payment")}
                    disabled={isSubmitting}
                    className="px-6 py-3 rounded-full font-semibold text-sm text-[#64748b] border border-[#e2e8f0] hover:bg-[#f8fafc] transition-colors disabled:opacity-60"
                  >
                    Back
                  </button>
                  <button
                    onClick={handlePlaceOrder}
                    disabled={isSubmitting}
                    className={cn(
                      "flex-1 inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-1 transition-all",
                      isSubmitting && "opacity-70 cursor-not-allowed"
                    )}
                  >
                    {isSubmitting ? (
                      <Loader2 className="w-4 h-4 animate-spin" />
                    ) : (
                      <Check className="w-4 h-4" />
                    )}
                    {isSubmitting
                      ? "Placing Order..."
                      : selectedPayment !== "cod"
                      ? "Place Order & Continue to Payment"
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
                    <p className="text-sm font-semibold text-[#0a1628]">{formatPrice(Number(item.line_total || 0))}</p>
                  </div>
                ))}
              </div>
              <div className="border-t border-[#e2e8f0] pt-4 space-y-2">
                <div className="flex justify-between text-sm">
                  <span className="text-[#64748b]">Subtotal</span>
                  <span className="font-medium">{formatPrice(subtotal)}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-[#64748b]">Shipping</span>
                  <span className="font-medium">
                    {shippingCost === 0 ? "Free" : formatPrice(shippingCost)}
                  </span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-[#64748b]">Tax (18%)</span>
                  <span className="font-medium">{formatPrice(taxAmount)}</span>
                </div>
                <div className="flex justify-between text-lg font-bold pt-2 border-t border-[#e2e8f0]">
                  <span>Total</span>
                  <span>{formatPrice(totalAmount)}</span>
                </div>
              </div>

              <div className="mt-6 space-y-2">
                <div className="flex items-center gap-2 text-xs text-[#64748b]">
                  <ShieldCheck className="w-4 h-4" />
                  Secure checkout
                </div>
                <div className="flex items-center gap-2 text-xs text-[#64748b]">
                  <Home className="w-4 h-4" />
                  Delivery across Uganda
                </div>
              </div>
            </div>
          </div>
        </div>
      </Container>
    </div>
  );
}
