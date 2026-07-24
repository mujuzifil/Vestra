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

export interface Customer {
  id: number;
  name: string;
  email: string;
  phone: string | null;
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
  is_default: boolean;
  created_at: string;
  updated_at: string;
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

