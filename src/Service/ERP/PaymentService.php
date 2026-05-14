<?php

namespace App\Service\ERP;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\SvgWriter;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class PaymentService
{
    // Stripe test publishable key (corresponding to the secret key)
    public const PUBLISHABLE_KEY = 'pk_test_51T4YBjE3rfLNFkiCYR3kv8swjhLpecomiUkxhvMe5EyKvLV9VdASFUiUahBHkAt9Z1sqmhAWw4vyuwIvFh6IapnT00U74YYgOx';

    public function __construct(
        private string $stripeSecretKey,
        private string $qrOutputDir
    ) {
        Stripe::setApiKey($this->stripeSecretKey);
    }

    /**
     * Creates a Stripe Checkout Session. Returns the session client_secret for embedded mode.
     */
    public function createEmbeddedSession(float $totalEur, string $returnUrl): ?string
    {
        if ($totalEur <= 0) return null;

        $amountCents = (int) round($totalEur * 100, 0, PHP_ROUND_HALF_UP);

        try {
            $session = Session::create([
                'mode'                => 'payment',
                'payment_method_types' => ['card'],
                'return_url'          => $returnUrl,
                'ui_mode'             => 'embedded_page',
                'line_items'          => [[
                    'quantity'   => 1,
                    'price_data' => [
                        'currency'     => 'eur',
                        'unit_amount'  => $amountCents,
                        'product_data' => ['name' => 'Achat FarmIA Desk'],
                    ],
                ]],
            ]);
            return $session->client_secret;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new \RuntimeException('Stripe: ' . $e->getMessage());
        }
    }

    /**
     * Creates a Stripe Checkout Session. Returns session URL or null on failure.
     */
    public function createCheckoutSession(float $totalEur, string $successUrl, string $cancelUrl): ?string
    {
        if ($totalEur <= 0) {
            file_put_contents(sys_get_temp_dir() . '/stripe_debug.txt', "FAIL: totalEur=$totalEur");
            return null;
        }

        $amountCents = (int) round($totalEur * 100, 0, PHP_ROUND_HALF_UP);
        file_put_contents(sys_get_temp_dir() . '/stripe_debug.txt', "key=" . substr($this->stripeSecretKey, 0, 10) . "... amount=$amountCents");

        try {
            $session = Session::create([
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'success_url' => $successUrl,
                'cancel_url'  => $cancelUrl,
                'line_items'  => [[
                    'quantity'   => 1,
                    'price_data' => [
                        'currency'     => 'usd',
                        'unit_amount'  => $amountCents,
                        'product_data' => ['name' => 'Abonnement FarmIA Desk'],
                    ],
                ]],
            ]);
            return $session->url;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            file_put_contents(sys_get_temp_dir() . '/stripe_debug.txt', "STRIPE ERROR: " . $e->getMessage(), FILE_APPEND);
            return null;
        } catch (\Exception $e) {
            file_put_contents(sys_get_temp_dir() . '/stripe_debug.txt', "\nGENERAL ERROR: " . $e->getMessage(), FILE_APPEND);
            return null;
        }
    }

    /**
     * Generates a QR code SVG from the URL (no GD/Imagick required).
     * Saves to public/uploads/erp_qr/ and returns the public-relative path.
     */
    public function generateQrCode(string $url): string
    {
        $dir = rtrim($this->qrOutputDir, '/\\');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'stripe_qr_' . md5($url) . '.svg';
        $fullPath = $dir . DIRECTORY_SEPARATOR . $filename;

        $result = Builder::create()
            ->writer(new SvgWriter())
            ->data($url)
            ->encoding(new Encoding('UTF-8'))
            ->size(260)
            ->margin(0)
            ->build();

        $result->saveToFile($fullPath);

        return 'uploads/erp_qr/' . $filename;
    }
}
