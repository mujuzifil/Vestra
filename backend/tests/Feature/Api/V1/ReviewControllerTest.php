<?php

namespace Tests\Feature\Api\V1;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function customer(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('customer');

        return $user;
    }

    private function admin(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('admin');

        return $user;
    }

    private function makePurchasedProduct(User $user): Product
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'paid',
            'payment_status' => 'paid',
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->price,
        ]);

        return $product;
    }

    public function test_guest_can_list_approved_reviews_for_product(): void
    {
        $product = Product::factory()->create();
        Review::factory()->count(3)->create([
            'product_id' => $product->id,
            'status' => 'approved',
            'is_hidden' => false,
        ]);
        Review::factory()->create([
            'product_id' => $product->id,
            'status' => 'pending',
            'is_hidden' => false,
        ]);

        $response = $this->getJson("/api/v1/products/{$product->slug}/reviews");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data.reviews');
    }

    public function test_customer_can_submit_review_for_purchased_product(): void
    {
        $user = $this->customer();
        $product = $this->makePurchasedProduct($user);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Excellent product',
            'comment' => 'Works great.',
            'pros' => ['Effective', 'Great scent'],
            'cons' => [],
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'status' => 'pending',
        ]);
    }

    public function test_customer_cannot_review_product_not_purchased(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/reviews', [
            'product_id' => $product->id,
            'rating' => 4,
            'comment' => 'Looks good.',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['product_id']);
    }

    public function test_customer_can_vote_review_helpful(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create();
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'status' => 'approved',
            'is_hidden' => false,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson("/api/v1/reviews/{$review->id}/helpful", [
            'is_helpful' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.helpful_count', 1);

        $this->assertDatabaseHas('review_helpful_votes', [
            'review_id' => $review->id,
            'user_id' => $user->id,
            'is_helpful' => true,
        ]);
    }

    public function test_customer_can_report_review(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create();
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'status' => 'approved',
            'is_hidden' => false,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson("/api/v1/reviews/{$review->id}/report", [
            'reason' => 'Spam',
            'details' => 'Irrelevant content',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('review_reports', [
            'review_id' => $review->id,
            'user_id' => $user->id,
            'reason' => 'Spam',
        ]);
    }

    public function test_admin_can_approve_review(): void
    {
        $admin = $this->admin();
        $product = Product::factory()->create();
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($admin, ['*']);

        $response = $this->putJson("/api/v1/admin/reviews/{$review->id}/status", [
            'status' => 'approved',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'approved');
    }

    public function test_admin_can_reply_to_review(): void
    {
        $admin = $this->admin();
        $product = Product::factory()->create();
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'status' => 'approved',
        ]);

        Sanctum::actingAs($admin, ['*']);

        $response = $this->postJson("/api/v1/admin/reviews/{$review->id}/reply", [
            'admin_reply' => 'Thank you for your feedback.',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.admin_reply.content', 'Thank you for your feedback.');
    }
}
