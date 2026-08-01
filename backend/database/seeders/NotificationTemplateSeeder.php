<?php

namespace Database\Seeders;

use App\Services\NotificationTemplateService;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(NotificationTemplateService::class);

        $templates = [
            [
                'event_key' => 'customer.registered',
                'name' => 'Customer Registered',
                'category' => 'customer',
                'subject' => 'Welcome to VESTRA, {{customer_name}}',
                'email_body' => '<p>Hi {{customer_name}},</p><p>Welcome to VESTRA Detergents. Your account has been created successfully.</p>',
                'sms_body' => 'Welcome to VESTRA Detergents, {{customer_name}}.',
                'in_app_body' => 'Welcome to VESTRA Detergents. Your account has been created successfully.',
                'variables_json' => ['customer_name', 'email'],
            ],
            [
                'event_key' => 'order.created',
                'name' => 'Order Created',
                'category' => 'order',
                'subject' => 'Your VESTRA Order {{order_number}} has been placed',
                'email_body' => '<p>Hi {{customer_name}},</p><p>Your order <strong>{{order_number}}</strong> has been placed successfully. Total: UGX {{amount}}.</p>',
                'sms_body' => 'VESTRA: Order {{order_number}} placed. Total UGX {{amount}}.',
                'in_app_body' => 'Your order {{order_number}} has been placed successfully.',
                'variables_json' => ['customer_name', 'order_number', 'invoice_number', 'amount'],
            ],
            [
                'event_key' => 'order.paid',
                'name' => 'Order Paid',
                'category' => 'order',
                'subject' => 'Payment Received for Order {{order_number}}',
                'email_body' => '<p>Hi {{customer_name}},</p><p>We received your payment of UGX {{amount}} for order <strong>{{order_number}}</strong>.</p>',
                'sms_body' => 'VESTRA: Payment received for order {{order_number}}. UGX {{amount}}.',
                'in_app_body' => 'Payment received for order {{order_number}}.',
                'variables_json' => ['customer_name', 'order_number', 'invoice_number', 'amount'],
            ],
            [
                'event_key' => 'order.shipped',
                'name' => 'Order Shipped',
                'category' => 'order',
                'subject' => 'Order {{order_number}} has been shipped',
                'email_body' => '<p>Hi {{customer_name}},</p><p>Your order <strong>{{order_number}}</strong> has been shipped. Tracking: {{tracking_number}}.</p>',
                'sms_body' => 'VESTRA: Order {{order_number}} shipped. Tracking: {{tracking_number}}.',
                'in_app_body' => 'Your order {{order_number}} has been shipped.',
                'variables_json' => ['customer_name', 'order_number', 'tracking_number'],
            ],
            [
                'event_key' => 'order.delivered',
                'name' => 'Order Delivered',
                'category' => 'order',
                'subject' => 'Order {{order_number}} delivered',
                'email_body' => '<p>Hi {{customer_name}},</p><p>Your order <strong>{{order_number}}</strong> has been delivered.</p>',
                'sms_body' => 'VESTRA: Order {{order_number}} delivered.',
                'in_app_body' => 'Your order {{order_number}} has been delivered.',
                'variables_json' => ['customer_name', 'order_number'],
            ],
            [
                'event_key' => 'order.cancelled',
                'name' => 'Order Cancelled',
                'category' => 'order',
                'subject' => 'Order {{order_number}} cancelled',
                'email_body' => '<p>Hi {{customer_name}},</p><p>Your order <strong>{{order_number}}</strong> has been cancelled. Reason: {{reason}}.</p>',
                'sms_body' => 'VESTRA: Order {{order_number}} cancelled.',
                'in_app_body' => 'Your order {{order_number}} has been cancelled.',
                'variables_json' => ['customer_name', 'order_number', 'reason'],
            ],
            [
                'event_key' => 'quote_request.admin_notification',
                'name' => 'New Quote Request Received',
                'category' => 'sales',
                'subject' => 'New Quote Request {{reference_number}} from {{company_name}}',
                'email_body' => '<p>A new quotation request has been submitted.</p><p><strong>Reference:</strong> {{reference_number}}<br><strong>Customer:</strong> {{customer_name}}<br><strong>Company:</strong> {{company_name}}<br><strong>Email:</strong> {{email}}<br><strong>Phone:</strong> {{phone}}<br><strong>Products:</strong> {{product_summary}}</p>',
                'sms_body' => 'VESTRA: New quote request {{reference_number}} from {{company_name}}.',
                'in_app_body' => 'New quote request {{reference_number}} from {{company_name}}.',
                'variables_json' => ['reference_number', 'customer_name', 'company_name', 'email', 'phone', 'product_summary'],
            ],
            [
                'event_key' => 'distributor.application_submitted',
                'name' => 'Distributor Application Submitted',
                'category' => 'distributor',
                'subject' => 'Distributor Application Received',
                'email_body' => '<p>Hi {{customer_name}},</p><p>Your distributor application for <strong>{{company_name}}</strong> has been received and is under review.</p>',
                'sms_body' => 'VESTRA: Distributor application for {{company_name}} received.',
                'in_app_body' => 'Your distributor application for {{company_name}} has been received.',
                'variables_json' => ['customer_name', 'company_name'],
            ],
            [
                'event_key' => 'distributor.application_admin_notification',
                'name' => 'New Distributor Application Received',
                'category' => 'distributor',
                'subject' => 'New Distributor Application {{reference_number}} from {{company_name}}',
                'email_body' => '<p>A new distributor application has been submitted.</p><p><strong>Reference:</strong> {{reference_number}}<br><strong>Contact Person:</strong> {{customer_name}}<br><strong>Company:</strong> {{company_name}}<br><strong>Email:</strong> {{email}}<br><strong>Phone:</strong> {{phone}}<br><strong>District:</strong> {{district}}<br><strong>Business Type:</strong> {{business_type}}</p>',
                'sms_body' => 'VESTRA: New distributor application {{reference_number}} from {{company_name}}.',
                'in_app_body' => 'New distributor application {{reference_number}} from {{company_name}}.',
                'variables_json' => ['reference_number', 'customer_name', 'company_name', 'email', 'phone', 'district', 'business_type'],
            ],
            [
                'event_key' => 'distributor.application_approved',
                'name' => 'Distributor Application Approved',
                'category' => 'distributor',
                'subject' => 'Distributor Application Approved',
                'email_body' => '<p>Hi {{customer_name}},</p><p>Your distributor application for <strong>{{company_name}}</strong> has been approved.</p>',
                'sms_body' => 'VESTRA: Distributor application for {{company_name}} approved.',
                'in_app_body' => 'Your distributor application for {{company_name}} has been approved.',
                'variables_json' => ['customer_name', 'company_name'],
            ],
            [
                'event_key' => 'distributor.application_rejected',
                'name' => 'Distributor Application Rejected',
                'category' => 'distributor',
                'subject' => 'Distributor Application Update',
                'email_body' => '<p>Hi {{customer_name}},</p><p>Your distributor application for <strong>{{company_name}}</strong> was not approved. Reason: {{reason}}.</p>',
                'sms_body' => 'VESTRA: Distributor application for {{company_name}} not approved.',
                'in_app_body' => 'Your distributor application for {{company_name}} was not approved.',
                'variables_json' => ['customer_name', 'company_name', 'reason'],
            ],
            [
                'event_key' => 'security.password_changed',
                'name' => 'Password Changed',
                'category' => 'security',
                'subject' => 'Your VESTRA password was changed',
                'email_body' => '<p>Hi {{customer_name}},</p><p>Your account password was changed. If this was not you, please contact support immediately.</p>',
                'sms_body' => 'VESTRA: Your password was changed.',
                'in_app_body' => 'Your password was changed.',
                'variables_json' => ['customer_name', 'email'],
            ],
            [
                'event_key' => 'admin.announcement',
                'name' => 'Admin Announcement',
                'category' => 'announcement',
                'subject' => '{{announcement_title}}',
                'email_body' => '<p>{{announcement_body}}</p>',
                'sms_body' => 'VESTRA: {{announcement_title}}',
                'in_app_body' => '{{announcement_body}}',
                'variables_json' => ['announcement_title', 'announcement_body', 'audience'],
            ],
            [
                'event_key' => 'system.maintenance_scheduled',
                'name' => 'System Maintenance Scheduled',
                'category' => 'system',
                'subject' => 'Scheduled Maintenance: {{window}}',
                'email_body' => '<p>Scheduled maintenance window: <strong>{{window}}</strong>.</p><p>{{description}}</p>',
                'sms_body' => 'VESTRA: Scheduled maintenance {{window}}.',
                'in_app_body' => 'Scheduled maintenance window: {{window}}.',
                'variables_json' => ['window', 'description'],
            ],
        ];

        foreach ($templates as $template) {
            $service->upsert($template);
        }
    }
}
