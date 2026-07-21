<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class ValidateMediaCommand extends Command
{
    protected $signature = 'media:validate';

    protected $description = 'Validate that every product image exists in storage and the storage symlink is correct.';

    public function handle(): int
    {
        $images = ProductImage::with('product')->orderBy('id')->get();

        if ($images->isEmpty()) {
            $this->warn('No product images found in the database.');

            return SymfonyCommand::SUCCESS;
        }

        $failures = 0;
        $publicStorageLink = public_path('storage');
        $expectedLinkTarget = storage_path('app/public');

        $this->info('Checking storage symlink...');

        if (! is_link($publicStorageLink) && ! is_dir($publicStorageLink)) {
            $this->error("public/storage is missing. Run: php artisan storage:link");
            $failures++;
        } elseif (is_link($publicStorageLink) && realpath(readlink($publicStorageLink)) !== realpath($expectedLinkTarget)) {
            $this->error("public/storage symlink points to the wrong target.");
            $this->error("  Expected: {$expectedLinkTarget}");
            $this->error("  Actual:   " . readlink($publicStorageLink));
            $failures++;
        } else {
            $this->info('✔ public/storage symlink is valid.');
        }

        $this->newLine();
        $this->info('Checking product images...');

        foreach ($images as $image) {
            $productName = $image->product?->name ?? "Product #{$image->product_id}";
            $path = $image->image;

            if (empty($path)) {
                $this->error("✖ {$productName} — no image path set.");
                $failures++;
                continue;
            }

            if (Storage::disk('public')->exists($path)) {
                $this->info("✔ {$productName} — {$path}");
            } else {
                $this->error("✖ {$productName} — {$path}");
                $this->error('   Missing: ' . Storage::disk('public')->path($path));
                $failures++;
            }
        }

        $this->newLine();

        if ($failures === 0) {
            $this->info('All product media validated successfully.');

            return SymfonyCommand::SUCCESS;
        }

        $this->error("Validation failed: {$failures} issue(s) found.");

        return SymfonyCommand::FAILURE;
    }
}
