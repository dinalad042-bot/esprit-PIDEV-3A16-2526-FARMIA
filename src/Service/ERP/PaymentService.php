<?php

namespace App\Service\ERP;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\SvgWriter;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class PaymentService
{
    public function __construct(
        private string $stripeSecretKey,
        private string $qrOutputDir
    ) {
        Stripe::setApiKey($this->stripeSecretKey);
    }

    /**
     * Creates a Stripe Checkout Session. Returns session URL or null on failure.
     */
    public function createCheckoutSession(float $totalEur, string $successUrl, string $cancelUrl): ?string
    {
        if ($totalEur <= 0) return null;

        $amountCents = (int) round($totalEur * 100, 0, PHP_ROUND_HALF_UP);

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
            error_log('[ERP] Stripe error: ' . $e->getMessage());
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
