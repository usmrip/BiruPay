<?php
namespace BiruPay\Exception;

/**
 * Exception yang dilempar saat order_id masih memiliki tagihan pending aktif
 */
class PendingPaymentException extends BiruPayException {
    public function getPaymentUrl(): ?string {
        return $this->response['data']['payment_url'] ?? null;
    }

    public function getSnapToken(): ?string {
        return $this->response['data']['snap_token'] ?? null;
    }

    public function getTrxReference(): ?string {
        return $this->response['data']['trx_reference'] ?? null;
    }
}

