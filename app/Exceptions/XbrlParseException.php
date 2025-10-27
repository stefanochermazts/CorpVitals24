<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class XbrlParseException extends Exception
{
    public function report(): void
    {
        Log::error('XBRL Parsing Exception', [
            'message' => $this->getMessage(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
        ]);
    }
}


