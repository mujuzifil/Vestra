<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\View;

class InvoicePdfService
{
    public function generate(Order $order): string
    {
        $settings = Setting::query()
            ->whereIn('key', ['company_name', 'business_address', 'support_email', 'support_phone', 'company_logo'])
            ->get()
            ->keyBy('key');

        $company = [
            'name' => $settings->get('company_name')?->typedValue() ?? 'VESTRA',
            'address' => $settings->get('business_address')?->typedValue() ?? 'Kampala, Uganda',
            'email' => $settings->get('support_email')?->typedValue() ?? 'vestradetergent@gmail.com',
            'phone' => $settings->get('support_phone')?->typedValue() ?? '+256 707 128 442',
            'logo' => $settings->get('company_logo')?->typedValue() ?? null,
        ];

        $html = View::make('invoices.pdf', [
            'order' => $order->load('items', 'user'),
            'company' => $company,
        ])->render();

        // If dompdf is available, use it; otherwise return HTML
        if (class_exists(\Dompdf\Dompdf::class)) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return $dompdf->output();
        }

        return $html;
    }

    public function getPath(Order $order): string
    {
        return storage_path("app/invoices/{$order->invoice_number}.pdf");
    }

    public function exists(Order $order): bool
    {
        return file_exists($this->getPath($order));
    }

    public function save(Order $order): string
    {
        $dir = storage_path('app/invoices');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $path = $this->getPath($order);
        $content = $this->generate($order);
        file_put_contents($path, $content);

        return $path;
    }
}
