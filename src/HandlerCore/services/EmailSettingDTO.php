<?php

namespace HandlerCore\services;

/**
 * This class represents the Data Transfer Object (DTO) for email settings.
 * It encapsulates the necessary configuration details for email communication.
 */
class EmailSettingDTO {
    private string $host;
    private string $user;
    private string $pass;
    private string $protocol;
    private int $port;

    public function __construct(string $host, string $user, string $pass, string $protocol, int $port) {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->protocol = $protocol;
        $this->port = $port;
    }

    public function getHost(): string {
        return $this->host;
    }

    public function getUser(): string {
        return $this->user;
    }

    public function getPassword(): string {
        return $this->pass;
    }

    public function getProtocol(): string {
        return $this->protocol;
    }

    public function getPort(): int {
        return $this->port;
    }
}