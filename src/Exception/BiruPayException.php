<?php
namespace BiruPay\Exception;

class BiruPayException extends \Exception {
    protected ?array $response = null;

    public function __construct(string $message = "", int $code = 0, ?array $response = null, ?\Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    public function getResponse(): ?array {
        return $this->response;
    }
}

