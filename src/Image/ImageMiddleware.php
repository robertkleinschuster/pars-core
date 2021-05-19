<?php

namespace Pars\Core\Image;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ImageMiddleware implements MiddlewareInterface
{
    protected ImageProcessor $imageProcessor;

    /**
     * ImageMiddleware constructor.
     * @param ImageProcessor $imageProcessor
     */
    public function __construct(ImageProcessor $imageProcessor)
    {
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            if ($this->imageProcessor->validateRequest($request)) {
                return $this->imageProcessor->getImageResponse($request);
            } else {
                $this->imageProcessor->displayPlaceholder($request, 'invalid request');
            }
        } catch (\Throwable $exception) {
            $this->imageProcessor->displayPlaceholder($request, $exception->getMessage());
        }
        return $handler->handle($request);
    }


}
