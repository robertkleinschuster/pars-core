<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-session for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-session/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-session/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Pars\Core\Authentication\Exception;

use Mezzio\Authentication\AuthenticationMiddleware;
use Mezzio\Session\SessionMiddleware;
use RuntimeException;

use function sprintf;

class MissingSessionContainerException extends RuntimeException implements ExceptionInterface
{
    public static function create() : self
    {
        return new self(sprintf(
            'Request is missing the attribute %s::SESSION_ATTRIBUTE ("%s"); '
            . 'perhaps you forgot to inject the %s prior to the %s?',
            SessionMiddleware::class,
            SessionMiddleware::SESSION_ATTRIBUTE,
            SessionMiddleware::class,
            AuthenticationMiddleware::class
        ));
    }
}
