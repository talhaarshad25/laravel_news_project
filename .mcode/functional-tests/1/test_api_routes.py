"""
Functional tests for public API routes.
These API endpoints existed in the origin (Laravel 8) and
should behave identically in the target (Laravel 9).

Entity classification: origin_and_target
"""
import os
import requests
import pytest

BASE_URL = os.environ.get("APP_BASE_URL", "http://127.0.0.1:8500")


class TestLatestNews:
    """GET /api/latest_news"""

    def test_latest_news_returns_200(self):
        resp = requests.get(f"{BASE_URL}/api/latest_news", timeout=10)
        assert resp.status_code == 200

    def test_latest_news_returns_content(self):
        resp = requests.get(f"{BASE_URL}/api/latest_news", timeout=10)
        assert len(resp.text) > 0


class TestPopularNews:
    """GET /api/popular_news"""

    def test_popular_news_returns_200(self):
        resp = requests.get(f"{BASE_URL}/api/popular_news", timeout=10)
        assert resp.status_code == 200

    def test_popular_news_returns_content(self):
        resp = requests.get(f"{BASE_URL}/api/popular_news", timeout=10)
        assert len(resp.text) > 0


class TestBreakingNews:
    """GET /api/breaking_news"""

    def test_breaking_news_returns_200(self):
        resp = requests.get(f"{BASE_URL}/api/breaking_news", timeout=10)
        assert resp.status_code == 200

    def test_breaking_news_returns_content(self):
        resp = requests.get(f"{BASE_URL}/api/breaking_news", timeout=10)
        assert len(resp.text) > 0


class TestNewsCategories:
    """GET /api/news_categories"""

    def test_news_categories_returns_200(self):
        resp = requests.get(f"{BASE_URL}/api/news_categories", timeout=10)
        assert resp.status_code == 200

    def test_news_categories_returns_content(self):
        resp = requests.get(f"{BASE_URL}/api/news_categories", timeout=10)
        assert len(resp.text) > 0
