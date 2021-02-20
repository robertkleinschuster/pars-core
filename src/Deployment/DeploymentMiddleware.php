<?php


namespace Pars\Core\Deployment;


use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\I18n\Translator\Translator;
use Laminas\I18n\Translator\TranslatorInterface;
use Pars\Model\Config\ConfigBeanFinder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeploymentMiddleware implements MiddlewareInterface
{
    protected array $config;
    protected Adapter $adapter;
    protected Translator $translator;

    /**
     * DeploymentMiddleware constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get('config');
        $this->adapter = $container->get(AdapterInterface::class);
        $this->translator = $container->get(TranslatorInterface::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $key = 'pars';
        try {
            $key = (new ConfigBeanFinder($this->adapter))->setConfig_Code('asset.key')->getBean()->get('Config_Value');
        } catch (\Throwable $exception){}
        if (isset($request->getQueryParams()['clearcache']) && $request->getQueryParams()['clearcache'] == $key) {
            $cache = new Cache($this->config, $this->adapter);
            $cache->setTranslator($this->translator);
            $cache->clear();
            $query = str_replace('&clearcache=' . $key, '', $request->getUri()->getQuery());
            $query = str_replace('?clearcache=' . $key, '', $query);
            $query = str_replace('clearcache=' . $key, '', $query);
            return new RedirectResponse($request->getUri()->withQuery($query));
        }
        return $handler->handle($request);
    }
}
