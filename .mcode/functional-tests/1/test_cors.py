"""
Functional tests for CORS middleware.
The built-in Laravel 9 HandleCors replaced fruitcake/laravel-cors.
These tests verify the CORS headers are properly set.

Entity classification: target_only (new middleware implementation)
"""
import os
import requests
import pytest

BASE_URL = os.environ.get("APP_BASE_URL", "http://127.0.0.1:8500")


class TestCorsPreflight:
    """OPTIONS preflight request to an API endpoint."""

    def test_cors_preflight_options_request(self):
        """Preflight OPTIONS returns Access-Control-Allow-Origin."""
        headers = {
            "Origin": "http://example.com",
            "Access-Control-Request-Method": "GET",
            "Access-Control-Request-Headers": "Content-Type",
        }
        resp = requests.options(f"{BASE_URL}/api/latest_news", headers=headers, timeout=10)
        # Laravel CORS should return 200 or 204 for preflight
        assert resp.status_code in (200, 204)
        acao = resp.headers.get("Access-Control-Allow-Origin", "")
        assert acao != "", "Access-Control-Allow-Origin header missing from preflight"


class TestCorsGetRequest:
    """GET request with Origin header should return CORS headers."""

    def test_cors_get_with_origin_header(self):
        """GET with Origin header returns Access-Control-Allow-Origin."""
        headers = {"Origin": "http://example.com"}
        resp = requests.get(f"{BASE_URL}/api/latest_news", headers=headers, timeout=10)
        assert resp.status_code == 200
        acao = resp.headers.get("Access-Control-Allow-Origin", "")
        assert acao != "", "Access-Control-Allow-Origin header missing"

    def test_cors_cross_origin_request(self):
        """Cross-origin request returns CORS headers."""
        headers = {"Origin": "http://another-domain.com"}
        resp = requests.get(f"{BASE_URL}/api/latest_news", headers=headers, timeout=10)
        acao = resp.headers.get("Access-Control-Allow-Origin", "")
        assert acao != "", "Access-Control-Allow-Origin header missing for cross-origin"
