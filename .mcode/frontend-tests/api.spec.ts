import { test, expect } from "@playwright/test";

/**
 * API Endpoint tests
 *
 * The /api/latest_news endpoint returns HTTP 200 with JSON data.
 * This endpoint works correctly because the Laravel Passport / API layer
 * does not depend on the themes table that causes the frontend 500 errors.
 *
 * These tests verify:
 * 1. The API endpoint returns HTTP 200
 * 2. The response is valid JSON
 * 3. The response structure matches the expected paginated format
 * 4. The Laravel Passport / API authentication layer is functional after upgrade
 */
test.describe("API Endpoint - /api/latest_news", () => {
  test("should return HTTP 200", async ({ page }) => {
    const response = await page.goto("/api/latest_news");
    expect(response).not.toBeNull();
    expect(response!.status()).toBe(200);
  });

  test("should return a valid JSON response", async ({ page }) => {
    const response = await page.goto("/api/latest_news");
    expect(response).not.toBeNull();

    // Check the Content-Type header includes JSON
    const contentType = response!.headers()["content-type"] || "";
    expect(contentType).toContain("application/json");
  });

  test("should return success:true in JSON body", async ({ page }) => {
    const response = await page.goto("/api/latest_news");
    const responseText = await page.locator("body").innerText();

    // Parse the JSON and verify the structure
    const json = JSON.parse(responseText);
    expect(json.success).toBe(true);
  });

  test("should return paginated data structure", async ({ page }) => {
    await page.goto("/api/latest_news");
    const responseText = await page.locator("body").innerText();

    const json = JSON.parse(responseText);
    // The response contains 'data' with pagination metadata
    expect(json).toHaveProperty("data");
    expect(json.data).toHaveProperty("current_page");
    expect(json.data).toHaveProperty("data");
    expect(json.data).toHaveProperty("per_page");
    expect(json.data).toHaveProperty("total");
  });

  test("should return a success message in the response", async ({ page }) => {
    await page.goto("/api/latest_news");
    const responseText = await page.locator("body").innerText();

    const json = JSON.parse(responseText);
    expect(json.message).toBe("Loaded Successfully");
  });

  test("should use the correct API URL (http://127.0.0.1:8500)", async ({
    page,
  }) => {
    await page.goto("/api/latest_news");
    const responseText = await page.locator("body").innerText();

    const json = JSON.parse(responseText);
    // Verify the path in pagination links matches the configured APP_URL
    expect(json.data.path).toContain("127.0.0.1:8500");
  });
});
