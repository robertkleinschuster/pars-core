<?php


namespace Pars\Core\Deployment;


use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeploymentMiddleware implements MiddlewareInterface
{
    protected array $config;

    /**
     * DeploymentMiddleware constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (isset($request->getQueryParams()['deploy'])) {
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/../data/cache/admin-config-cache.php')) {
                unlink($_SERVER['DOCUMENT_ROOT'] . '/../data/cache/admin-config-cache.php');
            }
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/../data/cache/frontend-config-cache.php')) {
                unlink($_SERVER['DOCUMENT_ROOT'] . '/../data/cache/frontend-config-cache.php');
            }
            if (isset($this->config['bundles'])) {
                foreach (array_column($this->config['bundles'], 'output') as $item) {
                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $item)) {
                        unlink($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $item);
                    }
                }
            }
            return new RedirectResponse('/');
        }
        return $handler->handle($request);
    }


}
