<?php
namespace BiruPay\Exception;

class BiruPayException extends \Exception {
    protected ?array $response = null;
    protected string $errorCode = '';

    public function __construct(string $message = "", int $code = 0, ?array $response = null, ?\Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
        $this->errorCode = (string)($response['code'] ?? '');
    }

    public function getResponse(): ?array {
        return $this->response;
    }

    public function getErrorCode(): string {
        return $this->errorCode;
    }

    public function getPaymentData(): ?array {
        return $this->response['data'] ?? null;
    }

    public function isAlreadyPaid(): bool {
        return ($this->errorCode === 'ALREADY_PAID') 
            || stripos($this->getMessage(), 'sudah pernah dibayar') !== false;
    }

    public function isAlreadyPending(): bool {
        return ($this->errorCode === 'ALREADY_PENDING');
    }
}
