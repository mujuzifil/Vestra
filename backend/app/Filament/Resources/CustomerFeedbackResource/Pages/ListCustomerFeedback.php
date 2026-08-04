<?php

namespace App\Filament\Resources\CustomerFeedbackResource\Pages;

use App\Filament\Pages\CustomerSuccess\FeedbackPage;
use App\Filament\Resources\CustomerFeedbackResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Legacy Customer Feedback index — permanently deferred to Customer Success → Feedback.
 */
class ListCustomerFeedback extends ListRecords
{
    protected static string $resource = CustomerFeedbackResource::class;

    public function mount(): void
    {
        $this->redirect(FeedbackPage::getUrl(), navigate: true);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
