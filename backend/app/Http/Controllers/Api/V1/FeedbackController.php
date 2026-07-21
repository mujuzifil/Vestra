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
        $feedback = new CustomerFeedback();
        $feedback->forceFill([
            'user_id' => $request->user()?->id,
            'category' => $request->validated('category'),
            'subject' => $request->validated('subject'),
            'message' => $request->validated('message'),
        ])->save();

        return $this->successResponse(
            $feedback,
            'Thank you for your feedback. We will review it shortly.',
            201
        );
    }

    public function adminIndex(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CustomerFeedback::class);

        $feedback = CustomerFeedback::with('user')
            ->latest()
            ->paginate($request->get('per_page', 20));

        return $this->successResponse($feedback);
    }

    public function updateStatus(Request $request, CustomerFeedback $feedback): JsonResponse
    {
        $this->authorize('moderate', $feedback);

        $data = $request->validate([
            'status' => 'required|in:new,in_progress,resolved',
        ]);

        $feedback->forceFill(['status' => $data['status']])->save();

        return $this->successResponse(
            $feedback->fresh(),
            'Feedback status updated successfully.'
        );
    }
}
