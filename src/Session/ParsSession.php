<?php


namespace Pars\Core\Session;


use Mezzio\Session\Session;
use Mezzio\Session\SessionInterface;
use Pars\Pattern\Exception\CoreException;

/**
 * Class ParsSession
 * @package Pars\Core\Session
 */
class ParsSession extends Session
{
    protected static ?SessionInterface $instance = null;


    /**
     * @return SessionInterface|null
     */
    public static function getInstance(): ?SessionInterface
    {
        if (self::$instance === null) {
            throw new CoreException('Static session instance not initialized.');
        }
        return self::$instance;
    }

    /**
     * @param SessionInterface $instance
     */
    public static function setInstance(SessionInterface $instance)
    {
        self::$instance = $instance;
    }

    /**
     * @return bool
     */
    public static function hasId(): bool
    {
        return self::getInstance()->getId() ? true : false;
    }
}
