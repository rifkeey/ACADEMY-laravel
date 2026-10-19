<?php

namespace App\Labs\Kql;

use RuntimeException;

/** A learner-facing query error (syntax or unknown column/table). Safe to show to the learner. */
class KqlError extends RuntimeException
{
    public function __construct(string $message, public readonly ?int $position = null)
    {
        parent::__construct($message);
    }
}
