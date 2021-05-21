<?php


namespace Pars\Core\Session;


use Mezzio\Session\LazySession;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionPersistenceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ParsSessionMiddleware extends \Mezzio\Session\SessionMiddleware
{
    /** @var SessionPersistenceInterface */
    private $persistence;

    public function __construct(SessionPersistenceInterface $persistence)
    {
        parent::__construct($persistence);
        $this->persistence = $persistence;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session  = new LazySession($this->persistence, $request);
        ParsSession::setInstance($session);
        $response = $handler->handle(
            $request
                ->withAttribute(self::SESSION_ATTRIBUTE, $session)
                ->withAttribute(SessionInterface::class, $session)
        );
        return $this->persistence->persistSession($session, $response);
    }



}
