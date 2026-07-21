import {
  AlertCircle,
  ArrowRight,
  Award,
  BadgeCheck,
  Check,
  ChevronRight,
  Clock,
  Droplets,
  Eye,
  FlaskConical,
  Globe,
  Home,
  Leaf,
  LucideIcon,
  Mail,
  MapPin,
  MessageCircle,
  Minus,
  Phone,
  Plus,
  Quote,
  Shield,
  ShieldCheck,
  Sparkles,
  Star,
  Target,
  Truck,
  Users,
} from "lucide-react";

const iconMap: Record<string, LucideIcon> = {
  AlertCircle,
  ArrowRight,
  Award,
  BadgeCheck,
  Check,
  ChevronRight,
  Clock,
  Droplets,
  Eye,
  FlaskConical,
  Globe,
  Home,
  Leaf,
  Mail,
  MapPin,
  MessageCircle,
  Minus,
  Phone,
  Plus,
  Quote,
  Shield,
  ShieldCheck,
  Sparkles,
  Star,
  Target,
  Truck,
  Users,
};

interface IconProps {
  name: string;
  className?: string;
}

export function Icon({ name, className }: IconProps) {
  const LucideIcon = iconMap[name];
  if (!LucideIcon) return null;
  return <LucideIcon className={className} />;
}
