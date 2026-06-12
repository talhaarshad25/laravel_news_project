"""
Functional tests for error handling.
Verify that non-existent routes return proper 404 responses.

Entity classification: target_only (validating error handler works with Laravel 9)
"""
import os
import requests
import pytest

BASE_URL = os.environ.get("APP_BASE_URL", "http://127.0.0.1:8500")


class TestApiNotFound:
    """GET /api/nonexistent -- API 404 handling."""

    def test_api_nonexistent_endpoint(self):
        """Non-existent API endpoint returns 404."""
        resp = requests.get(f"{BASE_URL}/api/nonexistent", timeout=10)
        assert resp.status_code == 404


class TestWebNotFound:
    """GET /nonexistent-page-xyz -- Web 404 handling."""

    def test_web_nonexistent_page(self):
        """Non-existent web page returns 404."""
        resp = requests.get(f"{BASE_URL}/nonexistent-page-xyz", timeout=10)
        assert resp.status_code == 404
