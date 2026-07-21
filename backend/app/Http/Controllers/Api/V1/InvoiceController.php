<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\InvoicePdfService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly InvoicePdfService $service) {}

    public function download(Request $request, int $order): StreamedResponse|JsonResponse
    {
        $orderModel = Order::with('items')->find($order);

        if (! $orderModel) {
            return $this->errorResponse('Order not found.', 404);
        }

        if ($orderModel->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized.', 403);
        }

        $path = $this->service->save($orderModel);

        return response()->streamDownload(function () use ($path) {
            echo file_get_contents($path);
        }, "{$orderModel->invoice_number}.pdf", [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
