<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreFeedbackRequest;
use App\Models\CustomerFeedback;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    use RespondsWithJson;

    public function store(StoreFeedbackRequest $request): JsonResponse
    {
        $feedback = CustomerFeedback::create([
            'user_id' => $request->user()?->id,
            'category' => $request->validated('category'),
            'subject' => $request->validated('subject'),
            'message' => $request->validated('message'),
        ]);

        return $this->successResponse(
            $feedback,
            'Thank you for your feedback. We will review it shortly.',
            201
        );
    }

    public function adminIndex(Request $request): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return $this->errorResponse('Unauthorized.', 403);
        }

        $feedback = CustomerFeedback::with('user')
            ->latest()
            ->paginate($request->get('per_page', 20));

        return $this->successResponse($feedback);
    }

    public function updateStatus(Request $request, CustomerFeedback $feedback): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return $this->errorResponse('Unauthorized.', 403);
        }

        $data = $request->validate([
            'status' => 'required|in:new,in_progress,resolved',
        ]);

        $feedback->update(['status' => $data['status']]);

        return $this->successResponse(
            $feedback->fresh(),
            'Feedback status updated successfully.'
        );
    }
}
