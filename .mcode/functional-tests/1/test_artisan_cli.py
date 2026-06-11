"""
Functional tests for artisan CLI commands.
Verifies that php artisan route:list works correctly.

Entity classification: origin_and_target
"""
import os
import subprocess
import glob
import pytest

WORKSPACE_DIR = os.environ.get(
    "WORKSPACE_DIR",
    "/Users/apple/.local/share/modelcode/workspace/jobs/c60d10a2-83af-44b0-a1d5-fb1894da39a2/workspace",
)
REPO_DIR = os.path.join(WORKSPACE_DIR, "laravel_news_project")

# Determine which PHP binary to use based on APP_BASE_URL
_PIXI_ENV_BASE = "/Users/apple/.local/share/modelcode/workspace/.pixi/envs"


def _find_php_binary():
    """Find the PHP binary from the pixi environment."""
    # Try target-app env first (for target runs), then app env (for origin)
    base_url = os.environ.get("APP_BASE_URL", "http://127.0.0.1:8500")
    if ":8001" in base_url:
        # Origin mode: use app env
        pattern = f"{_PIXI_ENV_BASE}/229-app-*/bin/php"
    else:
        # Target mode: use target-app env
        pattern = f"{_PIXI_ENV_BASE}/229-target-app-*/bin/php"

    matches = sorted(glob.glob(pattern))
    if matches:
        return matches[-1]  # Use latest version
    # Fallback: try any php in pixi
    matches = sorted(glob.glob(f"{_PIXI_ENV_BASE}/*/bin/php"))
    if matches:
        return matches[-1]
    return "php"


PHP_BIN = _find_php_binary()


def run_artisan(*args):
    """Helper to invoke php artisan and capture output."""
    result = subprocess.run(
        [PHP_BIN, "artisan", *args],
        cwd=REPO_DIR,
        capture_output=True,
        text=True,
        timeout=60,
    )
    return result


class TestRouteList:
    """php artisan route:list"""

    def test_route_list_exits_zero(self):
        result = run_artisan("route:list")
        assert result.returncode == 0, f"route:list failed: {result.stderr[:500]}"

    def test_route_list_contains_routes(self):
        result = run_artisan("route:list")
        assert result.returncode == 0
        # The output should contain route information
        output = result.stdout
        assert len(output) > 0, "route:list output is empty"
        # Should contain at least one of the known routes
        assert "GET" in output or "POST" in output, "No HTTP methods in route:list output"
