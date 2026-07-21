"use client";

import { InputHTMLAttributes, TextareaHTMLAttributes, SelectHTMLAttributes } from "react";
import { cn } from "@/lib/utils";

interface BaseProps {
  label: string;
  error?: string;
  id: string;
}

interface InputFieldProps extends BaseProps, Omit<InputHTMLAttributes<HTMLInputElement>, "id"> {}

export function InputField({ label, error, id, className, ...props }: InputFieldProps) {
  return (
    <div className="space-y-1.5">
      <label htmlFor={id} className="block text-sm font-semibold text-[#0a1628]">
        {label}
      </label>
      <input
        id={id}
        className={cn(
          "w-full px-4 py-3 rounded-xl border bg-[#f8fafc] text-[#0a1628] placeholder:text-[#94a3b8] focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all",
          error ? "border-red-400 focus:ring-red-400" : "border-[#e2e8f0]",
          className
        )}
        aria-invalid={error ? "true" : "false"}
        aria-describedby={error ? `${id}-error` : undefined}
        {...props}
      />
      {error && (
        <p id={`${id}-error`} className="text-sm text-red-500" role="alert">
          {error}
        </p>
      )}
    </div>
  );
}

interface TextareaFieldProps extends BaseProps, Omit<TextareaHTMLAttributes<HTMLTextAreaElement>, "id"> {}

export function TextareaField({ label, error, id, className, ...props }: TextareaFieldProps) {
  return (
    <div className="space-y-1.5">
      <label htmlFor={id} className="block text-sm font-semibold text-[#0a1628]">
        {label}
      </label>
      <textarea
        id={id}
        className={cn(
          "w-full px-4 py-3 rounded-xl border bg-[#f8fafc] text-[#0a1628] placeholder:text-[#94a3b8] focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all resize-none",
          error ? "border-red-400 focus:ring-red-400" : "border-[#e2e8f0]",
          className
        )}
        aria-invalid={error ? "true" : "false"}
        aria-describedby={error ? `${id}-error` : undefined}
        {...props}
      />
      {error && (
        <p id={`${id}-error`} className="text-sm text-red-500" role="alert">
          {error}
        </p>
      )}
    </div>
  );
}

interface SelectFieldProps extends BaseProps, Omit<SelectHTMLAttributes<HTMLSelectElement>, "id"> {
  options: { value: string; label: string }[];
}

export function SelectField({ label, error, id, options, className, ...props }: SelectFieldProps) {
  return (
    <div className="space-y-1.5">
      <label htmlFor={id} className="block text-sm font-semibold text-[#0a1628]">
        {label}
      </label>
      <select
        id={id}
        className={cn(
          "w-full px-4 py-3 rounded-xl border bg-[#f8fafc] text-[#0a1628] focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all",
          error ? "border-red-400 focus:ring-red-400" : "border-[#e2e8f0]",
          className
        )}
        aria-invalid={error ? "true" : "false"}
        aria-describedby={error ? `${id}-error` : undefined}
        {...props}
      >
        {options.map((option) => (
          <option key={option.value} value={option.value}>
            {option.label}
          </option>
        ))}
      </select>
      {error && (
        <p id={`${id}-error`} className="text-sm text-red-500" role="alert">
          {error}
        </p>
      )}
    </div>
  );
}
