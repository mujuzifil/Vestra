<?php

namespace App\Enums;

enum NotificationType: string
{
    case QUOTE_SUBMITTED = 'quote_submitted';
    case QUOTE_APPROVED = 'quote_approved';
    case QUOTE_DECLINED = 'quote_declined';
    case QUOTE_UPDATED = 'quote_updated';
    case DISTRIBUTOR_APPLICATION = 'distributor_application';
    case DISTRIBUTOR_APPROVED = 'distributor_approved';
    case DISTRIBUTOR_UPDATED = 'distributor_updated';
    case SUPPORT_TICKET = 'support_ticket';
    case SUPPORT_REPLY = 'support_reply';
    case BLOG_PUBLISHED = 'blog_published';
    case USER_CREATED = 'user_created';
    case USER_LOGIN = 'user_login';
    case SECURITY_ALERT = 'security_alert';
    case PRODUCT_UPDATED = 'product_updated';
    case INVENTORY_ALERT = 'inventory_alert';
    case PURCHASE_ORDER = 'purchase_order';
    case WORKFLOW = 'workflow';
    case SYSTEM = 'system';
    case TASK_ASSIGNED = 'task_assigned';
    case TASK_COMPLETED = 'task_completed';

    public function label(): string
    {
        return match ($this) {
            self::QUOTE_SUBMITTED => 'Quote Submitted',
            self::QUOTE_APPROVED => 'Quote Approved',
            self::QUOTE_DECLINED => 'Quote Declined',
            self::QUOTE_UPDATED => 'Quote Updated',
            self::DISTRIBUTOR_APPLICATION => 'Distributor Application',
            self::DISTRIBUTOR_APPROVED => 'Distributor Approved',
            self::DISTRIBUTOR_UPDATED => 'Distributor Updated',
            self::SUPPORT_TICKET => 'Support Ticket',
            self::SUPPORT_REPLY => 'Support Reply',
            self::BLOG_PUBLISHED => 'Blog Published',
            self::USER_CREATED => 'User Created',
            self::USER_LOGIN => 'User Login',
            self::SECURITY_ALERT => 'Security Alert',
            self::PRODUCT_UPDATED => 'Product Updated',
            self::INVENTORY_ALERT => 'Inventory Alert',
            self::PURCHASE_ORDER => 'Purchase Order',
            self::WORKFLOW => 'Workflow',
            self::SYSTEM => 'System',
            self::TASK_ASSIGNED => 'Task Assigned',
            self::TASK_COMPLETED => 'Task Completed',
        };
    }

    public function category(): NotificationCategory
    {
        return match ($this) {
            self::QUOTE_SUBMITTED,
            self::QUOTE_APPROVED,
            self::QUOTE_DECLINED,
            self::QUOTE_UPDATED => NotificationCategory::SALES,

            self::DISTRIBUTOR_APPLICATION,
            self::DISTRIBUTOR_APPROVED,
            self::DISTRIBUTOR_UPDATED => NotificationCategory::DISTRIBUTOR,

            self::SUPPORT_TICKET,
            self::SUPPORT_REPLY => NotificationCategory::CUSTOMER,

            self::BLOG_PUBLISHED => NotificationCategory::MARKETING,

            self::USER_CREATED,
            self::USER_LOGIN => NotificationCategory::CRM,

            self::SECURITY_ALERT => NotificationCategory::SECURITY,

            self::PRODUCT_UPDATED,
            self::INVENTORY_ALERT,
            self::PURCHASE_ORDER,
            self::WORKFLOW => NotificationCategory::OPERATIONS,

            self::TASK_ASSIGNED,
            self::TASK_COMPLETED => NotificationCategory::CRM,

            self::SYSTEM => NotificationCategory::SYSTEM,
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::QUOTE_SUBMITTED => 'heroicon-o-document-text',
            self::QUOTE_APPROVED => 'heroicon-o-check-badge',
            self::QUOTE_DECLINED => 'heroicon-o-x-circle',
            self::QUOTE_UPDATED => 'heroicon-o-arrow-path',
            self::DISTRIBUTOR_APPLICATION => 'heroicon-o-clipboard-document-list',
            self::DISTRIBUTOR_APPROVED => 'heroicon-o-check-circle',
            self::DISTRIBUTOR_UPDATED => 'heroicon-o-pencil-square',
            self::SUPPORT_TICKET => 'heroicon-o-lifebuoy',
            self::SUPPORT_REPLY => 'heroicon-o-chat-bubble-left-right',
            self::BLOG_PUBLISHED => 'heroicon-o-newspaper',
            self::USER_CREATED => 'heroicon-o-user-plus',
            self::USER_LOGIN => 'heroicon-o-arrow-right-on-rectangle',
            self::SECURITY_ALERT => 'heroicon-o-shield-exclamation',
            self::PRODUCT_UPDATED => 'heroicon-o-cube',
            self::INVENTORY_ALERT => 'heroicon-o-exclamation-triangle',
            self::PURCHASE_ORDER => 'heroicon-o-shopping-bag',
            self::WORKFLOW => 'heroicon-o-arrows-right-left',
            self::SYSTEM => 'heroicon-o-bell-alert',
            self::TASK_ASSIGNED => 'heroicon-o-user-circle',
            self::TASK_COMPLETED => 'heroicon-o-check-circle',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::QUOTE_SUBMITTED => 'info',
            self::QUOTE_APPROVED => 'success',
            self::QUOTE_DECLINED => 'danger',
            self::QUOTE_UPDATED => 'warning',
            self::DISTRIBUTOR_APPLICATION => 'info',
            self::DISTRIBUTOR_APPROVED => 'success',
            self::DISTRIBUTOR_UPDATED => 'warning',
            self::SUPPORT_TICKET => 'info',
            self::SUPPORT_REPLY => 'success',
            self::BLOG_PUBLISHED => 'purple',
            self::USER_CREATED => 'success',
            self::USER_LOGIN => 'info',
            self::SECURITY_ALERT => 'danger',
            self::PRODUCT_UPDATED => 'info',
            self::INVENTORY_ALERT => 'warning',
            self::PURCHASE_ORDER => 'info',
            self::WORKFLOW => 'gray',
            self::SYSTEM => 'primary',
            self::TASK_ASSIGNED => 'info',
            self::TASK_COMPLETED => 'success',
        };
    }

    public static function tryFromString(?string $value): ?self
    {
        if (empty($value)) {
            return null;
        }

        return self::tryFrom($value);
    }

    /**
     * @return array<int, self>
     */
    public static function filterOptions(): array
    {
        return self::cases();
    }
}
