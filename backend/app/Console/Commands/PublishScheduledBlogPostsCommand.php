<?php

namespace App\Console\Commands;

use App\Enums\BlogPostStatus;
use App\Models\BlogPost;
use App\Services\Catalog\CatalogSyncService;
use Illuminate\Console\Command;

class PublishScheduledBlogPostsCommand extends Command
{
    protected $signature = 'blog:publish-scheduled';

    protected $description = 'Publish blog posts whose scheduled_at time has arrived';

    public function handle(CatalogSyncService $sync): int
    {
        $posts = BlogPost::query()
            ->where('status', BlogPostStatus::SCHEDULED->value)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        $count = 0;

        foreach ($posts as $post) {
            $post->update([
                'status' => BlogPostStatus::PUBLISHED->value,
                'published_at' => $post->scheduled_at ?? now(),
            ]);

            $sync->syncBlog($post->id, $post->slug);
            $count++;
        }

        $this->info("Published {$count} scheduled blog post(s).");

        return self::SUCCESS;
    }
}
