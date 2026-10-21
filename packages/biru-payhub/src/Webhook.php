<?php
namespace BiruPay;

use BiruPay\Exception\BiruPayException;

class Webhook {
    private bool $isValid;
    private string $event;
    private array $data;
    private array $raw;

    public function __construct(bool $isValid, string $event, array $data, array $raw) {
        $this->isValid = $isValid;
        $this->event   = $event;
        $this->data    = $data;
        $this->raw     = $raw;
    }

    public static function verify(string $apiKey, ?string $rawPayload = null, ?string $signature = null): self {
        $raw = $rawPayload ?? file_get_contents('php://input');
        $sig = $signature ?? ($_SERVER['HTTP_X_BIRU_SIGNATURE'] ?? '');

        if (empty($raw)) {
            throw new BiruPayException('Payload webhook kosong.');
        }

        $expectedSig = hash_hmac('sha256', $raw, $apiKey);
        $isValid = hash_equals($expectedSig, $sig);

        if (!$isValid) {
            throw new BiruPayException('Signature webhook tidak valid.');
        }

        $decoded = json_decode($raw, true) ?: [];
        $event = $decoded['event'] ?? '';
        $data = $decoded['data'] ?? [];

        return new self($isValid, $event, $data, $decoded);
    }

    public function isValid(): bool {
        return $this->isValid;
    }

    public function isSuccess(): bool {
        return ($this->data['status'] ?? '') === 'success' || $this->event === 'payment.success';
    }

    public function isPending(): bool {
        return ($this->data['status'] ?? '') === 'pending' || $this->event === 'payment.pending';
    }

    public function isFailed(): bool {
        return in_array($this->data['status'] ?? '', ['failed', 'expired', 'cancel']);
    }

    public function getOrderId(): string {
        return (string) ($this->data['order_id'] ?? '');
    }

    public function getReference(): string {
        return (string) ($this->data['trx_reference'] ?? '');
    }

    public function getAmount(): float {
        return (float) ($this->data['amount'] ?? 0);
    }

    public function getGateway(): string {
        return (string) ($this->data['gateway'] ?? '');
    }

    public function getPaymentMethod(): string {
        return (string) ($this->data['payment_method'] ?? '');
    }

    public function getData(): array {
        return $this->data;
    }

    public function getRaw(): array {
        return $this->raw;
    }

    public function respondOk(): void {
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok']);
        exit;
    }
}

