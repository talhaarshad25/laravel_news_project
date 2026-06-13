"""Functional tests for CORS behavior on API endpoints.

Validates that the Access-Control-Allow-Origin header is returned
when an Origin header is provided in the request.
"""
import requests
import pytest


@pytest.fixture(autouse=True)
def health_check(base_url):
    """Confirm the app is reachable before running tests."""
    resp = requests.get(f"{base_url}/api/latest_news", timeout=10)
    assert resp.status_code == 200, f"Health check failed: {resp.status_code}"


class TestCorsHeaders:
    """CORS behavior on API endpoints."""

    def test_cors_header_on_get_request(self, base_url):
        """GET /api/latest_news with Origin header should return ACAO header."""
        headers = {"Origin": "http://example.com"}
        resp = requests.get(
            f"{base_url}/api/latest_news", headers=headers, timeout=10
        )
        assert resp.status_code == 200
        acao = resp.headers.get("Access-Control-Allow-Origin")
        assert acao is not None, "Access-Control-Allow-Origin header missing"
        assert acao == "*" or acao == "http://example.com"

    def test_cors_preflight_options(self, base_url):
        """OPTIONS /api/latest_news should return CORS preflight headers."""
        headers = {
            "Origin": "http://example.com",
            "Access-Control-Request-Method": "GET",
            "Access-Control-Request-Headers": "Content-Type",
        }
        resp = requests.options(
            f"{base_url}/api/latest_news", headers=headers, timeout=10
        )
        # 200 or 204 are both valid for CORS preflight responses
        assert resp.status_code in (200, 204)
        acao = resp.headers.get("Access-Control-Allow-Origin")
        assert acao is not None, "Access-Control-Allow-Origin header missing on preflight"

    def test_cors_header_on_categories(self, base_url):
        """GET /api/news_categories with Origin should return ACAO header."""
        headers = {"Origin": "http://example.com"}
        resp = requests.get(
            f"{base_url}/api/news_categories", headers=headers, timeout=10
        )
        assert resp.status_code == 200
        acao = resp.headers.get("Access-Control-Allow-Origin")
        assert acao is not None, "Access-Control-Allow-Origin header missing"

    def test_no_cors_without_origin(self, base_url):
        """GET /api/latest_news without Origin header may or may not return ACAO.
        This is a boundary test - behavior varies by CORS implementation."""
        resp = requests.get(f"{base_url}/api/latest_news", timeout=10)
        assert resp.status_code == 200
        # This is informational - we just verify the request succeeds
