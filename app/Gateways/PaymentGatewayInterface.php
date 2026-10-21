<?php
namespace App\Gateways;

use App\Core\Request;

interface PaymentGatewayInterface {
    /**
     * Create payment request with the payment gateway
     * @param array $params Contains transaction and customer details
     * @return array Standardized result array
     */
    public function createPayment(array $params): array;

    /**
     * Verify incoming webhook notification from the payment gateway
     * @param Request $request
     * @return array Standardized webhook validation result
     */
    public function verifyWebhook(Request $request): array;

    /**
     * Get gateway frontend configuration (e.g. client key, mode, script URL)
     * @return array
     */
    public function getFrontendConfig(): array;
}

