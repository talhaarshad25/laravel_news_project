"""Shared fixtures for functional tests."""
import os
import pytest


def pytest_addoption(parser):
    parser.addoption(
        "--base-url",
        action="store",
        default=os.environ.get("BASE_URL", "http://127.0.0.1:8500"),
        help="Base URL of the application under test",
    )


@pytest.fixture(scope="session")
def base_url(request):
    return request.config.getoption("--base-url")
