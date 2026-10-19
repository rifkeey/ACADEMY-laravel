<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A business rule stopped an action (exam not unlocked, enrolment closed, invalid discount...).
 * The message is written for the user and is safe to display.
 */
class RuleViolation extends RuntimeException {}
