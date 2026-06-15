<?php

namespace Tests\Feature\Api;

use App\Models\Maanuser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up Passport personal access client for testing.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Install Passport keys for token generation in tests
        $this->artisan('passport:keys', ['--force' => true]);

        // Create a personal access client needed for createToken() calls
        $clientRepository = app(ClientRepository::class);
        $clientRepository->createPersonalAccessClient(
            null,
            'Test Personal Access Client',
            'http://localhost'
        );
    }

    /**
     * Helper to build valid registration data.
     */
    private function validRegistrationData(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '1234567890',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ], $overrides);
    }

    /**
     * Helper to create a Maanuser directly in the database.
     */
    private function createUser(array $overrides = []): Maanuser
    {
        return Maanuser::create(array_merge([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'phone' => '9876543210',
            'email' => 'jane@example.com',
            'password' => Hash::make('secret123'),
        ], $overrides));
    }

    // ---------------------------------------------------------------
    //  Registration Tests
    // ---------------------------------------------------------------

    /**
     * Test that a user can register with valid data and receives a token.
     */
    public function test_user_can_register(): void
    {
        $data = $this->validRegistrationData();

        $response = $this->postJson('/api/user_register', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'user' => ['id', 'first_name', 'last_name', 'email', 'phone'],
                'access_token',
                'token_type',
                'expires_at',
            ])
            ->assertJson([
                'status' => 'Success',
                'message' => 'Registration Successful',
                'token_type' => 'Bearer',
            ]);

        // Verify the token is a non-empty string
        $this->assertNotEmpty($response->json('access_token'));

        // Verify the user was persisted to the database
        $this->assertDatabaseHas('maanusers', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
        ]);
    }

    /**
     * Test that registration fails when required fields are missing.
     */
    public function test_user_registration_fails_with_invalid_data(): void
    {
        // Completely empty payload
        $response = $this->postJson('/api/user_register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'first_name',
                'last_name',
                'phone',
                'email',
                'password',
            ]);
    }

    /**
     * Test that registration fails when the email is already taken.
     */
    public function test_user_registration_fails_with_duplicate_email(): void
    {
        $this->createUser(['email' => 'duplicate@example.com']);

        $data = $this->validRegistrationData(['email' => 'duplicate@example.com']);

        $response = $this->postJson('/api/user_register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test that registration fails when the phone is already taken.
     */
    public function test_user_registration_fails_with_duplicate_phone(): void
    {
        $this->createUser(['phone' => '5555555555']);

        $data = $this->validRegistrationData(['phone' => '5555555555']);

        $response = $this->postJson('/api/user_register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    /**
     * Test that registration fails when password confirmation does not match.
     */
    public function test_user_registration_fails_without_password_confirmation(): void
    {
        $data = $this->validRegistrationData([
            'password' => 'secret123',
            'password_confirmation' => 'different',
        ]);

        $response = $this->postJson('/api/user_register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test that registration fails when the password is too short.
     */
    public function test_user_registration_fails_with_short_password(): void
    {
        $data = $this->validRegistrationData([
            'password' => 'abc',
            'password_confirmation' => 'abc',
        ]);

        $response = $this->postJson('/api/user_register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test that registration fails with an invalid email format.
     */
    public function test_user_registration_fails_with_invalid_email(): void
    {
        $data = $this->validRegistrationData(['email' => 'not-an-email']);

        $response = $this->postJson('/api/user_register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test that the password is hashed (not stored in plain text).
     */
    public function test_user_registration_hashes_password(): void
    {
        $data = $this->validRegistrationData();

        $this->postJson('/api/user_register', $data)
            ->assertStatus(201);

        $user = Maanuser::where('email', 'john@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotEquals('secret123', $user->password);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    // ---------------------------------------------------------------
    //  Login Tests
    // ---------------------------------------------------------------

    /**
     * Test that a user can login with valid credentials and receives a token.
     */
    public function test_user_can_login(): void
    {
        $this->createUser([
            'email' => 'login@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/user_login', [
            'email' => 'login@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'user',
                'access_token',
                'token_type',
                'expires_at',
            ])
            ->assertJson([
                'status' => 'Success',
                'message' => 'Login Successful',
                'token_type' => 'Bearer',
            ]);

        $this->assertNotEmpty($response->json('access_token'));
    }

    /**
     * Test that login fails with an incorrect password.
     */
    public function test_user_login_fails_with_wrong_credentials(): void
    {
        $this->createUser([
            'email' => 'wrongpass@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/user_login', [
            'email' => 'wrongpass@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Password mismatch',
                'status' => 'false',
            ]);
    }

    /**
     * Test that login fails when the user does not exist.
     */
    public function test_user_login_fails_with_nonexistent_user(): void
    {
        $response = $this->postJson('/api/user_login', [
            'email' => 'nonexistent@example.com',
            'password' => 'secret123',
        ]);

        // Validation will reject because email does not exist in maanusers
        $response->assertStatus(422);
    }

    /**
     * Test that login fails when required fields are missing.
     */
    public function test_user_login_fails_with_missing_fields(): void
    {
        $response = $this->postJson('/api/user_login', []);

        $response->assertStatus(422);
    }

    // ---------------------------------------------------------------
    //  Protected Endpoint Access Tests
    // ---------------------------------------------------------------

    /**
     * Test that an authenticated user can access a protected endpoint.
     */
    public function test_authenticated_user_can_access_protected_endpoint(): void
    {
        $user = $this->createUser();

        Passport::actingAs($user, [], 'api');

        $response = $this->getJson('/api/bookmarks');

        $response->assertStatus(200);
    }

    /**
     * Test that an unauthenticated request to a protected endpoint is rejected.
     */
    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/bookmarks');

        $response->assertStatus(401);
    }

    /**
     * Test that an unauthenticated request to the update_profile endpoint is rejected.
     */
    public function test_unauthenticated_request_to_update_profile_is_rejected(): void
    {
        $response = $this->postJson('/api/update_profile', []);

        $response->assertStatus(401);
    }

    // ---------------------------------------------------------------
    //  Logout Tests
    // ---------------------------------------------------------------

    /**
     * Test that an authenticated user can logout successfully.
     */
    public function test_user_can_logout(): void
    {
        $user = $this->createUser();

        Passport::actingAs($user, [], 'api');

        $response = $this->getJson('/api/user_logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'Success',
                'message' => 'You have been successfully logged out',
            ]);
    }

    /**
     * Test that logout fails without authentication.
     */
    public function test_logout_fails_without_authentication(): void
    {
        $response = $this->getJson('/api/user_logout');

        $response->assertStatus(401);
    }

    // ---------------------------------------------------------------
    //  Token-Based Auth Flow (End-to-End) Tests
    // ---------------------------------------------------------------

    /**
     * Test the full flow: register, then use the returned token to access a protected endpoint.
     */
    public function test_register_token_grants_access_to_protected_endpoints(): void
    {
        $data = $this->validRegistrationData();

        $registerResponse = $this->postJson('/api/user_register', $data);
        $registerResponse->assertStatus(201);

        $token = $registerResponse->json('access_token');
        $this->assertNotEmpty($token);

        // Use the token from registration to access a protected route
        $protectedResponse = $this->getJson('/api/bookmarks', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $protectedResponse->assertStatus(200);
    }

    /**
     * Test the full flow: login, then use the returned token to access a protected endpoint.
     */
    public function test_login_token_grants_access_to_protected_endpoints(): void
    {
        $this->createUser([
            'email' => 'tokenflow@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $loginResponse = $this->postJson('/api/user_login', [
            'email' => 'tokenflow@example.com',
            'password' => 'secret123',
        ]);

        $loginResponse->assertStatus(200);

        $token = $loginResponse->json('access_token');
        $this->assertNotEmpty($token);

        // Use the token from login to access a protected route
        $protectedResponse = $this->getJson('/api/bookmarks', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $protectedResponse->assertStatus(200);
    }

    /**
     * Test that the registration response includes correct user data.
     */
    public function test_registration_response_contains_user_data(): void
    {
        $data = $this->validRegistrationData([
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice@example.com',
            'phone' => '1112223333',
        ]);

        $response = $this->postJson('/api/user_register', $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'first_name' => 'Alice',
                'last_name' => 'Smith',
                'email' => 'alice@example.com',
                'phone' => '1112223333',
            ]);

        // Password should NOT appear in the response (hidden attribute)
        $this->assertArrayNotHasKey('password', $response->json('user'));
    }

    /**
     * Test that the login response includes correct user data.
     */
    public function test_login_response_contains_user_data(): void
    {
        $this->createUser([
            'first_name' => 'Bob',
            'last_name' => 'Jones',
            'email' => 'bob@example.com',
            'phone' => '4445556666',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/user_login', [
            'email' => 'bob@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'first_name' => 'Bob',
                'last_name' => 'Jones',
                'email' => 'bob@example.com',
            ]);

        // Password should NOT appear in the response
        $this->assertArrayNotHasKey('password', $response->json('user'));
    }

    /**
     * Test that registration requires phone to be numeric.
     */
    public function test_user_registration_fails_with_non_numeric_phone(): void
    {
        $data = $this->validRegistrationData(['phone' => 'not-a-number']);

        $response = $this->postJson('/api/user_register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }
}
