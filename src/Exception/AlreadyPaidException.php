<?php
namespace BiruPay\Exception;

/**
 * Exception yang dilempar saat order_id sudah pernah dibayar lunas
 */
class AlreadyPaidException extends BiruPayException {
    public function getPaidAt(): ?string {
        return $this->response['data']['paid_at'] ?? null;
    }

    public function getTrxReference(): ?string {
        return $this->response['data']['trx_reference'] ?? null;
    }

    public function getPaymentMethod(): ?string {
        return $this->response['data']['payment_method'] ?? null;
    }

    public function getSuccessUrl(): ?string {
        return $this->response['data']['success_url'] ?? null;
    }
}
