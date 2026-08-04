<?php

namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Filament\Pages\Marketing\BlogPage;
use App\Filament\Resources\BlogPostResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Legacy Blog index — permanently deferred to Marketing → Blog workspace.
 */
class ListBlogPosts extends ListRecords
{
    protected static string $resource = BlogPostResource::class;

    public function mount(): void
    {
        $this->redirect(BlogPage::getUrl(), navigate: true);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
