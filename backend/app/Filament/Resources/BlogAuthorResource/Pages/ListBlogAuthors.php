<?php

namespace App\Filament\Resources\BlogAuthorResource\Pages;

use App\Filament\Resources\BlogAuthorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBlogAuthors extends ListRecords
{
    protected static string $resource = BlogAuthorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
