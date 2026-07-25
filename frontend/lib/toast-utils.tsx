import { toast } from "sonner";
import { ShoppingCart, Trash2, Check, AlertCircle, X } from "lucide-react";

export function toastAddedToCart(productName: string, quantity: number = 1) {
  toast.success("Added to cart", {
    description: `${quantity} × ${productName}`,
    icon: <ShoppingCart className="w-4 h-4" />,
  });
}

export function toastUpdatedQuantity(productName: string, quantity: number) {
  toast.info("Quantity updated", {
    description: `${productName} — quantity set to ${quantity}`,
    icon: <Check className="w-4 h-4" />,
  });
}

export function toastRemovedFromCart(productName: string) {
  toast("Removed from cart", {
    description: productName,
    icon: <Trash2 className="w-4 h-4" />,
  });
}

export function toastCartCleared() {
  toast("Cart cleared", {
    description: "All items have been removed from your cart.",
    icon: <X className="w-4 h-4" />,
  });
}

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

export function toastStockLimitReached(stock: number) {
  toast.warning("Stock limit reached", {
    description: `Only ${stock} unit${stock === 1 ? "" : "s"} available.`,
    icon: <AlertCircle className="w-4 h-4" />,
  });
}
