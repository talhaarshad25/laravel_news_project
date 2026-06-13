"""Functional tests for public API endpoints (no auth required).

Tests cover: latest_news, news_categories, popular_news, breaking_news,
latest_photo_galleries, video_galleries.
"""
import requests
import pytest


@pytest.fixture(autouse=True)
def health_check(base_url):
    """Confirm the app is reachable before running tests."""
    resp = requests.get(f"{base_url}/api/latest_news", timeout=10)
    assert resp.status_code == 200, f"Health check failed: {resp.status_code}"


class TestLatestNews:
    """GET /api/latest_news"""

    def test_latest_news_returns_200(self, base_url):
        resp = requests.get(f"{base_url}/api/latest_news", timeout=10)
        assert resp.status_code == 200
        body = resp.json()
        assert isinstance(body, (list, dict))

    def test_latest_news_not_found_method(self, base_url):
        resp = requests.post(f"{base_url}/api/latest_news", timeout=10)
        assert resp.status_code == 405


class TestNewsCategories:
    """GET /api/news_categories"""

    def test_news_categories_returns_200(self, base_url):
        resp = requests.get(f"{base_url}/api/news_categories", timeout=10)
        assert resp.status_code == 200
        body = resp.json()
        assert isinstance(body, (list, dict))


class TestPopularNews:
    """GET /api/popular_news"""

    def test_popular_news_returns_200(self, base_url):
        resp = requests.get(f"{base_url}/api/popular_news", timeout=10)
        assert resp.status_code == 200
        body = resp.json()
        assert isinstance(body, (list, dict))


class TestBreakingNews:
    """GET /api/breaking_news"""

    def test_breaking_news_returns_200(self, base_url):
        resp = requests.get(f"{base_url}/api/breaking_news", timeout=10)
        assert resp.status_code == 200
        body = resp.json()
        assert isinstance(body, (list, dict))


class TestPhotoGalleries:
    """GET /api/latest_photo_galleries"""

    def test_latest_photo_galleries_returns_200(self, base_url):
        resp = requests.get(f"{base_url}/api/latest_photo_galleries", timeout=10)
        assert resp.status_code == 200
        body = resp.json()
        assert isinstance(body, (list, dict))


class TestVideoGalleries:
    """GET /api/video_galleries"""

    def test_video_galleries_returns_200(self, base_url):
        resp = requests.get(f"{base_url}/api/video_galleries", timeout=10)
        assert resp.status_code == 200
        body = resp.json()
        assert isinstance(body, (list, dict))


class TestNotFoundEndpoint:
    """GET /api/nonexistent - expect 404"""

    def test_nonexistent_endpoint_returns_404(self, base_url):
        resp = requests.get(f"{base_url}/api/nonexistent_endpoint_xyz", timeout=10)
        assert resp.status_code == 404
