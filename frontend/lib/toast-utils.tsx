import { toast } from "sonner";
import { Check, AlertCircle } from "lucide-react";

export function toastError(message: string) {
  toast.error("Something went wrong", {
    description: message,
    icon: <AlertCircle className="w-4 h-4" />,
  });
}

export function toastSuccess(message: string) {
  toast.success(message, {
    icon: <Check className="w-4 h-4" />,
  });
}
