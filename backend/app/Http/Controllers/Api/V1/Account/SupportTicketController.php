<?php

namespace App\Http\Controllers\Api\V1\Account;

use App\Events\Account\SupportReplyCreated;
use App\Events\Account\SupportTicketCreated;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\SupportTicketResource;
use App\Models\SupportTicket;
use App\Services\SupportTicketService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupportTicketController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly SupportTicketService $service) {}

    public function index(Request $request): JsonResponse
    {
        $tickets = $this->service->listForUser($request->user(), $request->integer('per_page', 15));
        $resource = SupportTicketResource::collection($tickets)->response()->getData(true);

        return $this->successResponse([
            'data' => $resource['data'],
            'current_page' => $resource['meta']['current_page'],
            'last_page' => $resource['meta']['last_page'],
            'per_page' => $resource['meta']['per_page'],
            'total' => $resource['meta']['total'],
        ]);
    }

    public function show(Request $request, SupportTicket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);
        $ticket->load(['assignedStaff', 'replies.user', 'replies.staff']);

        return $this->successResponse(new SupportTicketResource($ticket));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'enquiry_type' => ['nullable', 'string', Rule::in(['general', 'sales', 'distributor', 'quote', 'technical_support', 'other'])],
            'message' => ['required', 'string', 'max:5000'],
            'priority' => ['nullable', 'string', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
        ]);

        $ticket = $this->service->create($request->user(), $data);

        SupportTicketCreated::dispatch($request->user(), $ticket);

        return $this->successResponse(new SupportTicketResource($ticket->load('replies')), 'Support ticket created.', 201);
    }

    public function reply(Request $request, SupportTicket $ticket): JsonResponse
    {
        $this->authorize('reply', $ticket);
        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
        ]);

        $reply = $this->service->reply($ticket, $request->user(), $data);

        SupportReplyCreated::dispatch($request->user(), $ticket, $reply);

        return $this->successResponse(new SupportTicketResource($ticket->fresh()->load(['assignedStaff', 'replies.user', 'replies.staff'])), 'Reply added.');
    }
}
