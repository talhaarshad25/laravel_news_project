<?php

namespace Tests\Feature;

use Tests\TestCase;

class CorsTest extends TestCase
{
    /**
     * Test that CORS headers are present on API responses when an Origin header is sent.
     *
     * @return void
     */
    public function test_cors_headers_present_on_api_response()
    {
        $response = $this->withHeaders([
            'Origin' => 'http://example.com',
        ])->get('/api/latest_news');

        $response->assertHeader('Access-Control-Allow-Origin');
    }

    /**
     * Test that a preflight OPTIONS request returns appropriate CORS headers.
     *
     * @return void
     */
    public function test_preflight_options_request_returns_cors_headers()
    {
        $response = $this->withHeaders([
            'Origin' => 'http://example.com',
            'Access-Control-Request-Method' => 'GET',
        ])->options('/api/latest_news');

        $response->assertHeader('Access-Control-Allow-Origin');
        $response->assertHeader('Access-Control-Allow-Methods');
        $response->assertHeader('Access-Control-Allow-Headers');
    }

    /**
     * Test that CORS allows all origins by returning Access-Control-Allow-Origin: *
     * since the config has allowed_origins set to ['*'].
     *
     * @return void
     */
    public function test_cors_allows_all_origins()
    {
        $origins = [
            'http://example.com',
            'https://another-domain.org',
            'http://localhost:3000',
        ];

        foreach ($origins as $origin) {
            $response = $this->withHeaders([
                'Origin' => $origin,
            ])->get('/api/latest_news');

            $response->assertHeader('Access-Control-Allow-Origin', '*');
        }
    }

    /**
     * Test that non-API routes do not have CORS headers since the paths config
     * only includes 'api/*' and 'sanctum/csrf-cookie'.
     *
     * @return void
     */
    public function test_non_api_routes_do_not_have_cors_headers()
    {
        $response = $this->withHeaders([
            'Origin' => 'http://example.com',
        ])->get('/');

        $response->assertHeaderMissing('Access-Control-Allow-Origin');
    }
}
