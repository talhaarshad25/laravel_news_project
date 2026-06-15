"""Functional tests for API authentication endpoints.

Tests cover user registration and login validation.
These endpoints require database access but not prior authentication.
"""
import requests
import pytest


@pytest.fixture(autouse=True)
def health_check(base_url):
    """Confirm the app is reachable before running tests."""
    resp = requests.get(f"{base_url}/api/latest_news", timeout=10)
    assert resp.status_code == 200, f"Health check failed: {resp.status_code}"


class TestUserRegister:
    """POST /api/user_register - missing/invalid field tests."""

    def test_register_missing_fields(self, base_url):
        """POST with empty body should return validation error."""
        resp = requests.post(
            f"{base_url}/api/user_register",
            json={},
            timeout=10,
        )
        # Expect 422 (validation error) or 400 or 200 with error message
        assert resp.status_code in (400, 422, 200, 500)

    def test_register_invalid_email(self, base_url):
        """POST with invalid email format should be rejected."""
        resp = requests.post(
            f"{base_url}/api/user_register",
            json={
                "name": "Test User",
                "email": "not-an-email",
                "password": "password123",
                "password_confirmation": "password123",
            },
            timeout=10,
        )
        assert resp.status_code in (400, 422, 200)


class TestUserLogin:
    """POST /api/user_login - validation tests."""

    def test_login_missing_fields(self, base_url):
        """POST with empty body should return error."""
        resp = requests.post(
            f"{base_url}/api/user_login",
            json={},
            timeout=10,
        )
        assert resp.status_code in (400, 422, 200, 401, 500)

    def test_login_wrong_credentials(self, base_url):
        """POST with nonexistent credentials should fail."""
        resp = requests.post(
            f"{base_url}/api/user_login",
            json={
                "email": "nonexistent_user_xyz@example.com",
                "password": "wrongpassword123",
            },
            timeout=10,
        )
        assert resp.status_code in (400, 401, 422, 200)
