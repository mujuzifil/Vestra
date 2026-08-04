<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class QuoteRequestsLegacyRedirectTest extends TestCase
{
    public function test_quote_requests_permanently_redirects_to_sales_quotes(): void
    {
        $this->get('/quote-requests')
            ->assertRedirect('/sales/quotes')
            ->assertStatus(301);
    }

    public function test_nested_quote_requests_path_permanently_redirects(): void
    {
        $this->get('/quote-requests/create')
            ->assertRedirect('/sales/quotes')
            ->assertStatus(301);
    }
}
