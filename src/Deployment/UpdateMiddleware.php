<?php


namespace Pars\Core\Deployment;


use GuzzleHttp\Psr7\Uri;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Response\TextResponse;
use Pars\Core\Container\ParsContainer;
use Pars\Core\Container\ParsContainerAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UpdateMiddleware implements MiddlewareInterface
{
    protected ParsUpdater $updater;
    use ParsContainerAwareTrait;

    /**
     * UpdateMiddleware constructor.
     */
    public function __construct(ParsContainer $parsContainer, ParsUpdater $updater)
    {
        $this->setParsContainer($parsContainer);
        $this->updater = $updater;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $params = $request->getQueryParams();
        $update = $params['update'] ?? false;
        $version = $params['version'] ?? false;
        $nopropagate = $params['nopropagate'] ?? false;
        if ($update) {
            $key = $this->getParsContainer()->getConfig()->getSecret();
            $keyNew = $this->getParsContainer()->getConfig()->getSecret(true);
            $redirectUri = Uri::withoutQueryValue($request->getUri(), 'update');
            if ($update == $key || $update == $keyNew) {
                if ($nopropagate) {
                    $this->updater->update();
                } else {
                    $this->getParsContainer()->getConfig()->generateSecret();
                    $this->updater->updateRemote();
                    $this->updater->update();
                }
                return new RedirectResponse($redirectUri);
            }
        }
        if ($version) {
            $key = $this->getParsContainer()->getConfig()->getSecret();
            $keyNew = $this->getParsContainer()->getConfig()->getSecret(true);
            if ($version == $key || $version == $keyNew) {
                return new TextResponse(PARS_VERSION);
            }
        }
        return $handler->handle($request);
    }

}
