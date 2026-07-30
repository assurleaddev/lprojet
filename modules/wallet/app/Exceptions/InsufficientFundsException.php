<?php

namespace Modules\Wallet\Exceptions;

use RuntimeException;

/**
 * Thrown when a wallet does not hold enough funds for the requested operation.
 *
 * This is an expected business failure: callers are meant to catch it and show
 * its message to the user, unlike unexpected failures which must bubble up.
 */
class InsufficientFundsException extends RuntimeException
{
}
