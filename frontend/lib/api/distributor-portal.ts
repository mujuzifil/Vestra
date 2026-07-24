import { apiGet, apiPost, apiPut, apiDelete, apiUpload, apiPostFormData } from "./client";
import type {
  ApiResponse,
  Distributor,
  DistributorBranch,
  DistributorContact,
  DistributorDocument,
  DistributorProduct,
  DistributorQuotation,
  DistributorOrder,
  DistributorInvoice,
  DistributorStatement,
  DistributorPaymentUpload,
  DistributorAnalytics,
  DistributorNotification,
  DistributorDashboard,
  DistributorRequest,
} from "@/types";

const PREFIX = "/distributor";

// Dashboard
export async function getDistributorDashboard(): Promise<DistributorDashboard> {
  const response = await apiGet<ApiResponse<DistributorDashboard>>(`${PREFIX}/dashboard`);
  return response.data;
}

// Profile / company
export async function getDistributorProfile(): Promise<Distributor> {
  const response = await apiGet<ApiResponse<Distributor>>(`${PREFIX}/profile`);
  return response.data;
}

export async function updateDistributorProfile(data: Partial<Distributor>): Promise<Distributor> {
  const response = await apiPut<ApiResponse<Distributor>, Partial<Distributor>>(`${PREFIX}/profile`, data);
  return response.data;
}

export async function uploadDistributorLogo(file: File): Promise<Distributor> {
  const response = await apiUpload<ApiResponse<Distributor>>(`${PREFIX}/profile/logo`, file, "logo");
  return response.data;
}

export async function removeDistributorLogo(): Promise<void> {
  await apiDelete<ApiResponse<void>>(`${PREFIX}/profile/logo`);
}

// Branches
export type CreateBranchData = Omit<DistributorBranch, "id" | "created_at" | "updated_at">;

export async function getDistributorBranches(): Promise<DistributorBranch[]> {
  const response = await apiGet<ApiResponse<DistributorBranch[]>>(`${PREFIX}/branches`);
  return response.data;
}

export async function getDistributorBranch(id: number): Promise<DistributorBranch> {
  const response = await apiGet<ApiResponse<DistributorBranch>>(`${PREFIX}/branches/${id}`);
  return response.data;
}

export async function createDistributorBranch(data: CreateBranchData): Promise<DistributorBranch> {
  const response = await apiPost<ApiResponse<DistributorBranch>, CreateBranchData>(`${PREFIX}/branches`, data);
  return response.data;
}

export async function updateDistributorBranch(id: number, data: Partial<DistributorBranch>): Promise<DistributorBranch> {
  const response = await apiPut<ApiResponse<DistributorBranch>, Partial<DistributorBranch>>(`${PREFIX}/branches/${id}`, data);
  return response.data;
}

export async function deleteDistributorBranch(id: number): Promise<void> {
  await apiDelete<ApiResponse<void>>(`${PREFIX}/branches/${id}`);
}

// Contacts
export type CreateContactData = Omit<DistributorContact, "id" | "created_at" | "updated_at">;

export async function getDistributorContacts(): Promise<DistributorContact[]> {
  const response = await apiGet<ApiResponse<DistributorContact[]>>(`${PREFIX}/contacts`);
  return response.data;
}

export async function getDistributorContact(id: number): Promise<DistributorContact> {
  const response = await apiGet<ApiResponse<DistributorContact>>(`${PREFIX}/contacts/${id}`);
  return response.data;
}

export async function createDistributorContact(data: CreateContactData): Promise<DistributorContact> {
  const response = await apiPost<ApiResponse<DistributorContact>, CreateContactData>(`${PREFIX}/contacts`, data);
  return response.data;
}

export async function updateDistributorContact(id: number, data: Partial<DistributorContact>): Promise<DistributorContact> {
  const response = await apiPut<ApiResponse<DistributorContact>, Partial<DistributorContact>>(`${PREFIX}/contacts/${id}`, data);
  return response.data;
}

export async function deleteDistributorContact(id: number): Promise<void> {
  await apiDelete<ApiResponse<void>>(`${PREFIX}/contacts/${id}`);
}

// Documents
export type CreateDocumentData = { title: string; type: string; file: File };

export async function getDistributorDocuments(): Promise<DistributorDocument[]> {
  const response = await apiGet<ApiResponse<DistributorDocument[]>>(`${PREFIX}/documents`);
  return response.data;
}

export async function uploadDistributorDocument(data: CreateDocumentData): Promise<DistributorDocument> {
  const formData = new FormData();
  formData.append("title", data.title);
  formData.append("type", data.type);
  formData.append("file", data.file);

  const response = await apiPostFormData<ApiResponse<DistributorDocument>>(`${PREFIX}/documents`, formData);
  return response.data;
}

export async function deleteDistributorDocument(id: number): Promise<void> {
  await apiDelete<ApiResponse<void>>(`${PREFIX}/documents/${id}`);
}

// Products
export async function getDistributorProducts(): Promise<DistributorProduct[]> {
  const response = await apiGet<ApiResponse<DistributorProduct[]>>(`${PREFIX}/products`);
  return response.data;
}

export async function getDistributorProduct(slug: string): Promise<DistributorProduct> {
  const response = await apiGet<ApiResponse<DistributorProduct>>(`${PREFIX}/products/${slug}`);
  return response.data;
}

// Quotes
export interface CreateQuoteLine {
  product_id: number;
  quantity: number;
  unit_price: string;
}

export interface CreateQuoteData {
  notes?: string | null;
  items: CreateQuoteLine[];
}

export async function getDistributorQuotes(): Promise<DistributorQuotation[]> {
  const response = await apiGet<ApiResponse<DistributorQuotation[]>>(`${PREFIX}/quotes`);
  return response.data;
}

export async function getDistributorQuote(id: number): Promise<DistributorQuotation> {
  const response = await apiGet<ApiResponse<DistributorQuotation>>(`${PREFIX}/quotes/${id}`);
  return response.data;
}

export async function createDistributorQuote(data: CreateQuoteData): Promise<DistributorQuotation> {
  const response = await apiPost<ApiResponse<DistributorQuotation>, CreateQuoteData>(`${PREFIX}/quotes`, data);
  return response.data;
}

export async function updateDistributorQuote(id: number, data: Partial<DistributorQuotation>): Promise<DistributorQuotation> {
  const response = await apiPut<ApiResponse<DistributorQuotation>, Partial<DistributorQuotation>>(`${PREFIX}/quotes/${id}`, data);
  return response.data;
}

export async function deleteDistributorQuote(id: number): Promise<void> {
  await apiDelete<ApiResponse<void>>(`${PREFIX}/quotes/${id}`);
}

export async function submitDistributorQuote(id: number): Promise<DistributorQuotation> {
  const response = await apiPost<ApiResponse<DistributorQuotation>, Record<string, never>>(`${PREFIX}/quotes/${id}/submit`, {});
  return response.data;
}

export async function acceptDistributorQuote(id: number): Promise<DistributorQuotation> {
  const response = await apiPost<ApiResponse<DistributorQuotation>, Record<string, never>>(`${PREFIX}/quotes/${id}/accept`, {});
  return response.data;
}

// Orders
export async function getDistributorOrders(): Promise<DistributorOrder[]> {
  const response = await apiGet<ApiResponse<DistributorOrder[]>>(`${PREFIX}/orders`);
  return response.data;
}

export async function getDistributorOrder(id: number): Promise<DistributorOrder> {
  const response = await apiGet<ApiResponse<DistributorOrder>>(`${PREFIX}/orders/${id}`);
  return response.data;
}

// Invoices
export async function getDistributorInvoices(): Promise<DistributorInvoice[]> {
  const response = await apiGet<ApiResponse<DistributorInvoice[]>>(`${PREFIX}/invoices`);
  return response.data;
}

export async function getDistributorInvoice(id: number): Promise<DistributorInvoice> {
  const response = await apiGet<ApiResponse<DistributorInvoice>>(`${PREFIX}/invoices/${id}`);
  return response.data;
}

// Statements
export async function getDistributorStatement(params?: { start?: string; end?: string }): Promise<DistributorStatement> {
  const searchParams = params ? new URLSearchParams(params as Record<string, string>).toString() : "";
  const path = searchParams ? `${PREFIX}/statements?${searchParams}` : `${PREFIX}/statements`;
  const response = await apiGet<ApiResponse<DistributorStatement>>(path);
  return response.data;
}

// Payments
export type CreatePaymentUploadData = Omit<DistributorPaymentUpload, "id" | "file_url" | "status" | "verification_notes" | "verified_at" | "created_at" | "updated_at"> & { receipt?: File };

export async function getDistributorPayments(): Promise<DistributorPaymentUpload[]> {
  const response = await apiGet<ApiResponse<DistributorPaymentUpload[]>>(`${PREFIX}/payments`);
  return response.data;
}

export async function createDistributorPayment(data: CreatePaymentUploadData): Promise<DistributorPaymentUpload> {
  if (!data.receipt) {
    throw new Error("Payment receipt file is required.");
  }

  const formData = new FormData();
  formData.append("amount", data.amount);
  formData.append("currency", data.currency);
  formData.append("reference_number", data.reference_number);
  if (data.notes) formData.append("notes", data.notes);
  formData.append("file", data.receipt);

  const response = await apiPostFormData<ApiResponse<DistributorPaymentUpload>>(`${PREFIX}/payments`, formData);
  return response.data;
}

// Analytics
export async function getDistributorAnalytics(): Promise<DistributorAnalytics> {
  const response = await apiGet<ApiResponse<DistributorAnalytics>>(`${PREFIX}/analytics`);
  return response.data;
}

// Notifications
export async function getDistributorNotifications(): Promise<DistributorNotification[]> {
  const response = await apiGet<ApiResponse<DistributorNotification[]>>(`${PREFIX}/notifications`);
  return response.data;
}

// Application status
export async function getDistributorApplicationStatus(): Promise<DistributorRequest | null> {
  const response = await apiGet<ApiResponse<DistributorRequest | null>>("/distributor/application-status");
  return response.data;
}
