<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class CustomizeJsonFormatter
{
    /** @param array<string,mixed> $config */
    public function __invoke(Logger $logger, array $config = []): void
    {
        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof StreamHandler) {
                $handler->setFormatter(new JsonFormatter(JsonFormatter::BATCH_MODE_JSON, true));
            }
        }
    }
}


