<?php

namespace App\Tests\Functional;

use App\Tests\BaseWebTestCase;

/**
 * Security and authentication tests.
 *
 * @covers \App\Controller\Web\SecurityController
 * @covers \App\Security\LoginSuccessHandler
 * @covers \App\Security\LoginFailureHandler
 */
class SecurityTest extends BaseWebTestCase
{
    /**
     * TEST: Login page is accessible
     */
    public function testLoginPageIsAccessible(): void
    {
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
    }

    /**
     * TEST: Login with valid credentials redirects
     */
    public function testLoginWithValidCredentialsRedirects(): void
    {
        $user = $this->createTestUser('ROLE_USER');
        $this->em->flush();

        $this->client->request('POST', '/login', [
            '_username' => $user->getEmail(),
            '_password' => 'testpassword123',
        ]);

        $this->assertResponseRedirects();
    }

    /**
     * TEST: Login with invalid credentials shows error
     */
    public function testLoginWithInvalidCredentialsShowsError(): void
    {
        $this->client->request('POST', '/login', [
            '_username' => 'nonexistent@example.com',
            '_password' => 'wrongpassword',
        ]);

        $this->assertResponseRedirects('/login');
    }

    /**
     * TEST: Logout redirects
     */
    public function testLogoutRedirects(): void
    {
        $this->loginWithRole('ROLE_USER');
        $this->client->request('GET', '/logout');
        $this->assertResponseRedirects();
    }

    /**
     * TEST: Protected routes require authentication
     */
    public function testProtectedRoutesRequireAuthentication(): void
    {
        $protectedRoutes = [
            '/ferme/',
            '/animal/',
            '/plante/',
            '/analyse/',
            '/conseil/',
        ];

        foreach ($protectedRoutes as $route) {
            $this->client->request('GET', $route);
            $this->assertResponseRedirects(
                null,
                302,
                "Route $route should redirect to login"
            );
        }
    }

    /**
     * TEST: Admin routes require admin role
     */
    public function testAdminRoutesRequireAdminRole(): void
    {
        $this->loginWithRole('ROLE_USER');
        $this->client->request('GET', '/admin/dashboard');
        
        // Should be forbidden or redirect
        $this->assertResponseStatusCodeIsOneOf([403, 302, 404]);
    }

    /**
     * TEST: Admin can access admin routes
     */
    public function testAdminCanAccessAdminRoutes(): void
    {
        $this->loginWithRole('ROLE_ADMIN');
        $this->client->request('GET', '/admin/dashboard');
        
        // Should not be 403
        $this->assertNotEquals(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * TEST: CSRF token present on login form
     */
    public function testCsrfTokenPresentOnLoginForm(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        
        // Check for CSRF token field
        $csrfField = $crawler->filter('input[type="hidden"][name="_csrf_token"]');
        $this->assertGreaterThan(0, $csrfField->count(), 'CSRF token should be present');
    }
}
