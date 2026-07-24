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
      <label htmlFor={id} className="block text-sm font-semibold text-text-heading">
        {label}
      </label>
      <input
        id={id}
        className={cn(
          "w-full px-4 py-3 rounded-xl border bg-neutral-50 text-text-heading placeholder:text-text-placeholder focus:outline-none focus:ring-2 focus:ring-secondary-500 focus:border-transparent transition-all-base",
          error ? "border-danger-400 focus:ring-danger-400" : "border-border-default",
          className
        )}
        aria-invalid={error ? "true" : "false"}
        aria-describedby={error ? `${id}-error` : undefined}
        {...props}
      />
      {error && (
        <p id={`${id}-error`} className="text-sm text-danger-500" role="alert">
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
      <label htmlFor={id} className="block text-sm font-semibold text-text-heading">
        {label}
      </label>
      <textarea
        id={id}
        className={cn(
          "w-full px-4 py-3 rounded-xl border bg-neutral-50 text-text-heading placeholder:text-text-placeholder focus:outline-none focus:ring-2 focus:ring-secondary-500 focus:border-transparent transition-all-base resize-none",
          error ? "border-danger-400 focus:ring-danger-400" : "border-border-default",
          className
        )}
        aria-invalid={error ? "true" : "false"}
        aria-describedby={error ? `${id}-error` : undefined}
        {...props}
      />
      {error && (
        <p id={`${id}-error`} className="text-sm text-danger-500" role="alert">
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
      <label htmlFor={id} className="block text-sm font-semibold text-text-heading">
        {label}
      </label>
      <select
        id={id}
        className={cn(
          "w-full px-4 py-3 rounded-xl border bg-neutral-50 text-text-heading focus:outline-none focus:ring-2 focus:ring-secondary-500 focus:border-transparent transition-all-base",
          error ? "border-danger-400 focus:ring-danger-400" : "border-border-default",
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
        <p id={`${id}-error`} className="text-sm text-danger-500" role="alert">
          {error}
        </p>
      )}
    </div>
  );
}
