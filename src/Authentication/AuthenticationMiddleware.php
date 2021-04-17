<?php

namespace Pars\Core\Authentication;

use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Mezzio\Csrf\CsrfMiddleware;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Session\SessionMiddleware;
use Pars\Core\Logging\LoggingMiddleware;
use Pars\Helper\Path\PathHelper;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class AuthenticationMiddleware
 * @package Pars\Core\Authentication
 */
class AuthenticationMiddleware implements MiddlewareInterface
{

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @var AuthenticationInterface
     */
    private AuthenticationInterface $auth;

    /**
     * @var PathHelper
     */
    private PathHelper $pathHelper;

    /**
     * AuthenticationMiddleware constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->auth = $container->get(AuthenticationInterface::class);
        $this->pathHelper = clone $container->get(PathHelper::class);
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        $guard = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);
        $flash = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
        $config = $this->container->get('config');

        try {
            $redirect = $this->pathHelper
                ->setController($config['authentication']['redirect']['controller'])
                ->setAction($config['authentication']['redirect']['action'])
                ->getPath();
        } catch (\Exception $exception) {
            $redirect = $config['authentication']['redirect']['path'] ?? '';
        }

        $redirectOrig = $redirect;
        $whitelist = [];
        $whitelist[] = $this->normalizePath($redirect);
        foreach ($config['authentication']['whitelist'] as $item) {
            try {
                $path = $this->pathHelper->setController($item['controller'])->setAction($item['action'])->getPath();
            } catch (\Exception $exception) {
                $path = $item['path'] ?? '';
            }
            $whitelist[] = $this->normalizePath($path);
        }

        try {
            $currentPath = $this->pathHelper->getUrlHelper()->generate();
        } catch (\Exception $exception) {
            $currentPath = $request->getUri()->getPath();
        }
        $current = $this->normalizePath($currentPath);
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $log = $request->getAttribute(LoggingMiddleware::LOGGER_ATTRIBUTE);
        try {
            $user = $this->auth->authenticate($request);

            // Validation CSRF Token
            if ($request->getMethod() === 'POST' && $user === null) {
                if (isset($request->getParsedBody()['login_token']) && $guard->validateToken($request->getParsedBody()['login_token'] ?? '', 'login_token')) {
                    $user = $this->auth->authenticate($request);
                    if ($user === null) {
                        $flash->flash('login_error', 'credentials');
                        $redirect = $currentPath;
                    }
                    $session->unset('locale');
                } else {
                    $flash->flash('login_error', 'token');
                    $redirect = $currentPath;
                }
            }
        } catch (\Throwable $exception) {
            $session->clear();
            $log->error('Authentication Error', ['exception' => $exception]);
        }

        if ($user !== null) {
            return $handler->handle($request->withAttribute(UserInterface::class, $user));
        }

        if (
               (in_array($current, $whitelist)  && $current !== $this->normalizePath($redirectOrig) ) // e.g. setup
            || ($request->getMethod() === 'GET' && $current === $this->normalizePath($redirectOrig)) // login screen
        ) {
            return $handler->handle($request);
        }
        $session->set('requested_path', (string) $request->getUri());
        return new RedirectResponse($redirect);
    }


    protected function normalizePath(string $path)
    {
        return trim(strtolower(str_replace('/', '', $path)));
    }
}
