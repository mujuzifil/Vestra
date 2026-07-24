export interface ApiResponse<T> {
  success: boolean;
  data: T;
  message: string;
}

export interface Category {
  id: number;
  name: string;
  slug: string;
  description: string;
  sort_order: number;
  status: string;
}

export interface ProductImage {
  id: number;
  image: string;
  alt_text: string | null;
  sort_order: number;
}

export interface Product {
  id: number;
  category_id: number;
  category: Category;
  name: string;
  slug: string;
  short_description: string;
  description: string;
  features: string[] | null;
  benefits: string[] | null;
  specifications: Record<string, string> | null;
  sku: string;
  price: string;
  featured: boolean;
  status: string;
  stock_quantity: number;
  meta_title: string | null;
  meta_description: string | null;
  images: ProductImage[];
  created_at: string;
  updated_at: string;
}

export interface Setting {
  key: string;
  value: string | null;
  type: string;
  group: string;
  label: string;
}

export interface NavLink {
  label: string;
  href: string;
  children?: NavLink[];
}

export interface Feature {
  icon: string;
  title: string;
  description: string;
}

export interface PromiseItem {
  icon: string;
  title: string;
  description: string;
}

export interface CoreValue {
  icon: string;
  title: string;
  description: string;
}

export interface FaqItem {
  question: string;
  answer: string;
}

export interface DistributorBenefit {
  icon: string;
  title: string;
  description: string;
}

export interface ContactInfo {
  phone: string;
  email: string;
  location: string;
  whatsapp: string;
  businessHours: string;
}

export interface ContactFormData {
  name: string;
  email: string;
  subject: string;
  message: string;
}

export interface DistributorFormData {
  fullName: string;
  businessName: string;
  email: string;
  phone: string;
  city: string;
  businessType: string;
  experience: string;
}

export interface CustomerPreferences {
  email_marketing: boolean;
  sms_notifications: boolean;
  order_updates_email: boolean;
  order_updates_sms: boolean;
  promotional_emails: boolean;
  two_factor_enabled: boolean;
  login_alerts: boolean;
  profile_visibility: "public" | "private" | "friends";
  language: string;
  currency: string;
}

export interface Customer {
  id: number;
  name: string;
  first_name: string | null;
  last_name: string | null;
  email: string;
  phone: string | null;
  date_of_birth: string | null;
  gender: "male" | "female" | "other" | "prefer_not_to_say" | null;
  avatar_url: string | null;
  preferences: CustomerPreferences | null;
  is_admin: boolean;
  roles?: string[];
  must_change_password?: boolean;
  created_at: string;
  updated_at: string;
}

export interface AuthResponse {
  user: Customer;
  token: string;
  exchange_token?: string | null;
  role: "customer" | "administrator" | "super-administrator";
  redirect_to: string;
  must_change_password: boolean;
}

export interface Address {
  id: number;
  label: string;
  full_name: string;
  phone: string;
  city: string;
  region: string | null;
  district: string | null;
  address_line: string;
  address_line_2: string | null;
  postal_code: string | null;
  country: string | null;
  delivery_notes: string | null;
  is_default: boolean;
  is_default_shipping: boolean;
  is_default_billing: boolean;
  created_at: string;
  updated_at: string;
}

export interface ActivityItem {
  id: number | string;
  type: "login" | "password_change" | "profile_update" | "address_added" | "address_updated" | "address_deleted" | "order_placed" | "order_paid" | "order_shipped" | "order_delivered" | "preference_update" | "account_deletion_requested" | string;
  description: string;
  metadata?: Record<string, unknown> | null;
  ip_address?: string | null;
  user_agent?: string | null;
  created_at: string;
}

export interface CartItemProduct {
  id: number;
  name: string;
  slug: string;
  sku: string;
  price: string;
  stock_quantity: number;
  images: ProductImage[];
}

export interface CartItem {
  id: number;
  product: CartItemProduct;
  quantity: number;
  line_total: string;
}

export interface Cart {
  id: number;
  items: CartItem[];
  item_count: number;
  subtotal: string;
}

export interface OrderItem {
  id: number;
  product_id: number;
  product_name: string;
  product_sku: string;
  unit_price: string;
  quantity: number;
  line_total: string;
}

export interface Order {
  id: number;
  invoice_number: string;
  status: string;
  payment_method: string;
  payment_status: string;
  shipping_address: Record<string, string>;
  subtotal: string;
  shipping_cost: string;
  tax_amount: string;
  total_amount: string;
  notes: string | null;
  payment_url?: string;
  courier: string | null;
  tracking_number: string | null;
  dispatched_at: string | null;
  delivered_at: string | null;
  estimated_delivery: string | null;
  timeline: TimelineEvent[];
  items: OrderItem[];
  created_at: string;
  updated_at: string;
}

export interface TimelineEvent {
  icon?: string;
  color?: string;
  title: string;
  description: string;
  time: string;
  actor: string;
}



// Distributor portal types

export interface DistributorRequest {
  id: number;
  full_name: string;
  business_name: string;
  email: string;
  phone: string;
  city: string;
  business_type: string;
  experience: string;
  status: "pending" | "approved" | "rejected";
  admin_notes: string | null;
  created_at: string;
  updated_at: string;
}

export interface Distributor {
  id: number;
  company_name: string;
  trading_name: string | null;
  registration_number: string | null;
  tax_identification: string | null;
  vat_number: string | null;
  business_type: string | null;
  industry: string | null;
  years_in_business: number | null;
  company_size: string | null;
  website: string | null;
  primary_contact_name: string | null;
  email: string;
  phone: string | null;
  country: string | null;
  district: string | null;
  city: string | null;
  address: string | null;
  postal_address: string | null;
  logo_url: string | null;
  operating_hours_json: Record<string, unknown> | null;
  bank_info_json: Record<string, unknown> | null;
  billing_info_json: Record<string, unknown> | null;
  expected_monthly_volume: string | null;
  products_of_interest: string | null;
  status: "active" | "suspended";
  approved_at: string | null;
  suspended_at: string | null;
  created_at: string;
  updated_at: string;
  credit_account?: CreditAccountSummary | null;
}

export interface CreditAccountSummary {
  id: number;
  limit: string;
  balance: string;
  authorized_amount: string;
  available_credit: string;
  utilization_percentage: number;
  status: string;
}

export interface DistributorBranch {
  id: number;
  name: string;
  manager_name: string | null;
  phone: string | null;
  email: string | null;
  country: string | null;
  district: string | null;
  city: string | null;
  address: string | null;
  latitude: string | null;
  longitude: string | null;
  delivery_notes: string | null;
  status: string;
  is_default: boolean;
  created_at: string;
  updated_at: string;
}

export interface DistributorContact {
  id: number;
  name: string;
  role: string | null;
  phone: string | null;
  email: string | null;
  permissions_json: string[] | null;
  is_primary: boolean;
  created_at: string;
  updated_at: string;
}

export interface DistributorDocument {
  id: number;
  title: string;
  type: string;
  file_url: string;
  version: number;
  uploaded_by: string | null;
  created_at: string;
  updated_at: string;
}

export interface DistributorProductPrice {
  price: string;
  effective_from: string | null;
  effective_until: string | null;
}

export interface DistributorProduct {
  id: number;
  category_id: number;
  category: Category;
  name: string;
  slug: string;
  short_description: string;
  description: string;
  features: string[] | null;
  benefits: string[] | null;
  specifications: Record<string, string> | null;
  sku: string;
  price: string;
  distributor_price: string | null;
  negotiated_price: string | null;
  tier_price: string | null;
  moq: number | null;
  featured: boolean;
  status: string;
  stock_quantity: number;
  images: ProductImage[];
  created_at: string;
  updated_at: string;
}

export interface QuotationItem {
  id: number;
  product_id: number;
  product_name: string;
  product_sku: string;
  quantity: number;
  unit_price: string;
  line_total: string;
}

export interface DistributorQuotation {
  id: number;
  reference_number: string;
  status: "draft" | "submitted" | "reviewed" | "quoted" | "accepted" | "rejected" | "converted_to_order";
  notes: string | null;
  admin_notes: string | null;
  subtotal: string;
  tax_amount: string;
  total_amount: string;
  submitted_at: string | null;
  quoted_at: string | null;
  expires_at: string | null;
  items: QuotationItem[];
  created_at: string;
  updated_at: string;
}

export interface DistributorOrder {
  id: number;
  invoice_number: string;
  status: string;
  payment_method: string;
  payment_status: string;
  shipping_address: Record<string, string>;
  subtotal: string;
  shipping_cost: string;
  tax_amount: string;
  distributor_discount_amount: string;
  total_amount: string;
  notes: string | null;
  courier: string | null;
  tracking_number: string | null;
  dispatched_at: string | null;
  delivered_at: string | null;
  estimated_delivery: string | null;
  timeline: TimelineEvent[];
  items: OrderItem[];
  created_at: string;
  updated_at: string;
}

export interface DistributorInvoice {
  id: number;
  invoice_number: string;
  order_id: number | null;
  status: string;
  amount: string;
  amount_paid: string;
  balance_due: string;
  due_date: string | null;
  paid_at: string | null;
  created_at: string;
  updated_at: string;
}

export interface StatementTransaction {
  date: string;
  description: string;
  invoice_number: string | null;
  debit: string;
  credit: string;
  balance: string;
}

export interface DistributorStatement {
  opening_balance: string;
  closing_balance: string;
  period_start: string;
  period_end: string;
  transactions: StatementTransaction[];
}

export interface DistributorPaymentUpload {
  id: number;
  amount: string;
  currency: string;
  reference_number: string;
  file_url: string;
  notes: string | null;
  status: "pending" | "verified" | "rejected";
  verification_notes: string | null;
  verified_at: string | null;
  created_at: string;
  updated_at: string;
}

export interface DistributorAnalytics {
  total_orders: number;
  total_revenue: string;
  total_quotes: number;
  pending_quotes: number;
  average_order_value: string;
  month_over_month_growth: number;
  top_products: Array<{
    product_id: number;
    product_name: string;
    total_quantity: number;
    total_revenue: string;
  }>;
  orders_by_status: Record<string, number>;
  revenue_by_month: Array<{ month: string; revenue: string }>;
}

export interface DistributorNotification {
  id: number | string;
  type: string;
  title: string;
  message: string;
  is_read: boolean;
  action_url: string | null;
  created_at: string;
}

export interface DistributorDashboard {
  distributor: Distributor;
  stats: {
    total_orders: number;
    pending_orders: number;
    total_quotes: number;
    pending_quotes: number;
    unread_notifications: number;
    credit_limit: string;
    available_credit: string;
    outstanding_balance: string;
  };
  recent_orders: DistributorOrder[];
  recent_quotes: DistributorQuotation[];
  recent_notifications: DistributorNotification[];
}
