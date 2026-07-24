"use client";

import { useRef, useState } from "react";
import { Upload, FileText, X, Loader2 } from "lucide-react";
import { InputField, SelectField } from "@/components/common/form-field";
import { toastSuccess, toastError } from "@/lib/toast-utils";

interface DocumentUploaderProps {
  onUpload: (data: { title: string; type: string; file: File }) => Promise<void>;
  types?: { value: string; label: string }[];
}

const defaultTypes = [
  { value: "", label: "Select document type" },
  { value: "trade_license", label: "Trade License" },
  { value: "tax_certificate", label: "Tax Certificate" },
  { value: "bank_statement", label: "Bank Statement" },
  { value: "identity", label: "Identity Document" },
  { value: "contract", label: "Contract / Agreement" },
  { value: "other", label: "Other" },
];

export function DocumentUploader({ onUpload, types = defaultTypes }: DocumentUploaderProps) {
  const [title, setTitle] = useState("");
  const [type, setType] = useState("");
  const [file, setFile] = useState<File | null>(null);
  const [isUploading, setIsUploading] = useState(false);
  const inputRef = useRef<HTMLInputElement>(null);

  function handleFileChange(e: React.ChangeEvent<HTMLInputElement>) {
    if (e.target.files && e.target.files[0]) {
      setFile(e.target.files[0]);
    }
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (!title || !type || !file) {
      toastError("Please provide a title, document type, and file.");
      return;
    }

    setIsUploading(true);
    try {
      await onUpload({ title, type, file });
      setTitle("");
      setType("");
      setFile(null);
      if (inputRef.current) inputRef.current.value = "";
      toastSuccess("Document uploaded successfully.");
    } catch (err) {
      const message = err instanceof Error ? err.message : "Upload failed.";
      toastError(message);
    } finally {
      setIsUploading(false);
    }
  }

  return (
    <form onSubmit={handleSubmit} className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
      <h3 className="text-lg font-bold text-[#0a1628] mb-4">Upload Document</h3>
      <div className="grid sm:grid-cols-2 gap-5 mb-4">
        <InputField
          id="doc-title"
          label="Document Title"
          value={title}
          onChange={(e) => setTitle(e.target.value)}
          placeholder="e.g. Trade License 2026"
        />
        <SelectField
          id="doc-type"
          label="Document Type"
          options={types}
          value={type}
          onChange={(e) => setType(e.target.value)}
        />
      </div>

      <div className="mb-4">
        <label className="block text-sm font-semibold text-[#0a1628] mb-1.5">File</label>
        <div
          onClick={() => inputRef.current?.click()}
          className="cursor-pointer border-2 border-dashed border-[#e2e8f0] rounded-xl p-6 hover:border-green-500 hover:bg-green-50/30 transition-colors"
        >
          <div className="flex flex-col items-center text-center">
            <Upload className="w-8 h-8 text-[#94a3b8] mb-2" />
            <p className="text-sm font-medium text-[#0a1628]">
              {file ? file.name : "Click to upload or drag and drop"}
            </p>
            <p className="text-xs text-[#94a3b8] mt-1">PDF, JPG, PNG up to 10MB</p>
          </div>
          <input
            ref={inputRef}
            type="file"
            accept=".pdf,.jpg,.jpeg,.png"
            onChange={handleFileChange}
            className="hidden"
          />
        </div>
        {file && (
          <div className="flex items-center gap-2 mt-3 p-2 rounded-lg bg-[#f8fafc] text-sm text-[#0a1628]">
            <FileText className="w-4 h-4 text-green-600" />
            <span className="flex-1 truncate">{file.name}</span>
            <button
              type="button"
              onClick={() => {
                setFile(null);
                if (inputRef.current) inputRef.current.value = "";
              }}
              className="p-1 text-[#94a3b8] hover:text-red-600"
              aria-label="Remove file"
            >
              <X className="w-4 h-4" />
            </button>
          </div>
        )}
      </div>

      <button
        type="submit"
        disabled={isUploading || !title || !type || !file}
        className="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 disabled:opacity-60 disabled:cursor-not-allowed transition-colors"
      >
        {isUploading ? <Loader2 className="w-4 h-4 animate-spin" /> : <Upload className="w-4 h-4" />}
        {isUploading ? "Uploading..." : "Upload Document"}
      </button>
    </form>
  );
}
