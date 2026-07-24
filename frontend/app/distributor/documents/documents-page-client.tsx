"use client";

import { FileText, Loader2, Trash2, ExternalLink } from "lucide-react";
import { EmptyState } from "@/components/common/empty-state";
import { DocumentUploader } from "@/components/distributor/document-uploader";
import { useDistributorDocuments } from "@/hooks/use-distributor-documents";
import { toastSuccess, toastError } from "@/lib/toast-utils";

const documentTypes = [
  { value: "", label: "Select document type" },
  { value: "trade_license", label: "Trade License" },
  { value: "tax_certificate", label: "Tax Certificate" },
  { value: "bank_statement", label: "Bank Statement" },
  { value: "identity", label: "Identity Document" },
  { value: "contract", label: "Contract / Agreement" },
  { value: "other", label: "Other" },
];

export function DocumentsPageClient() {
  const { data: documents, isLoading, upload, remove } = useDistributorDocuments();

  async function handleUpload(data: { title: string; type: string; file: File }) {
    try {
      await upload(data);
    } catch (err) {
      toastError(err instanceof Error ? err.message : "Upload failed.");
      throw err;
    }
  }

  async function handleDelete(id: number) {
    if (!confirm("Delete this document?")) return;
    try {
      await remove(id);
      toastSuccess("Document deleted.");
    } catch (err) {
      toastError(err instanceof Error ? err.message : "Delete failed.");
    }
  }

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl font-extrabold text-[#0a1628]">Documents</h1>
        <p className="text-[#64748b]">Upload and manage company documents.</p>
      </div>

      <DocumentUploader onUpload={handleUpload} types={documentTypes} />

      {isLoading ? (
        <div className="min-h-[200px] flex items-center justify-center">
          <Loader2 className="w-8 h-8 animate-spin text-green-500" />
        </div>
      ) : !documents || documents.length === 0 ? (
        <EmptyState title="No documents yet" description="Upload your first document above." />
      ) : (
        <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead className="bg-[#f8fafc]">
                <tr>
                  <th className="px-6 py-4 font-semibold text-[#0a1628]">Title</th>
                  <th className="px-6 py-4 font-semibold text-[#0a1628]">Type</th>
                  <th className="px-6 py-4 font-semibold text-[#0a1628]">Version</th>
                  <th className="px-6 py-4 font-semibold text-[#0a1628]">Uploaded</th>
                  <th className="px-6 py-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody>
                {documents.map((doc) => (
                  <tr key={doc.id} className="border-t border-[#f1f5f9]">
                    <td className="px-6 py-4">
                      <div className="flex items-center gap-2">
                        <FileText className="w-4 h-4 text-green-600" />
                        <span className="font-medium text-[#0a1628]">{doc.title}</span>
                      </div>
                    </td>
                    <td className="px-6 py-4 text-[#64748b] capitalize">{doc.type.replace(/_/g, " ")}</td>
                    <td className="px-6 py-4 text-[#64748b]">{doc.version}</td>
                    <td className="px-6 py-4 text-[#64748b]">{new Date(doc.created_at).toLocaleDateString()}</td>
                    <td className="px-6 py-4 text-right">
                      <div className="flex items-center justify-end gap-2">
                        <a
                          href={doc.file_url}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-green-700 bg-green-50 rounded-lg hover:bg-green-100"
                        >
                          <ExternalLink className="w-3 h-3" />
                          View
                        </a>
                        <button
                          type="button"
                          onClick={() => handleDelete(doc.id)}
                          className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 rounded-lg hover:bg-red-100"
                        >
                          <Trash2 className="w-3 h-3" />
                          Delete
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  );
}
