<?php


namespace Pars\Core\Translation\Provider\Libretranslate;


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use Pars\Core\Config\ParsConfig;
use Pars\Core\Localization\LocaleInterface;
use Pars\Core\Translation\Provider\Base\AbstractTranslationProvider;
use Pars\Helper\String\StringHelper;

class LibretranslateTranslationProvider extends AbstractTranslationProvider
{
    protected ParsConfig $config;

    /**
     * LibretranslateTranslationProvider constructor.
     * @param ParsConfig $config
     */
    public function __construct(ParsConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @return ParsConfig
     */
    public function getConfig(): ParsConfig
    {
        return $this->config;
    }


    protected function getClient()
    {
        return new Client();
    }

    /***
     * @param string $text
     * @param LocaleInterface $from
     * @param LocaleInterface $to
     * @return string
     */
    public function translate(string $text, LocaleInterface $from, LocaleInterface $to): string
    {
        if ($from->getLocale_Language() === $to->getLocale_Language()) {
            return $text;
        }
        $result = $text;
        try {
            $client = $this->getClient();
            $response = $client->post(
                $this->getEndpointTranslate(),
                [
                    RequestOptions::JSON => [
                        'q' => StringHelper::stripString($text),
                        'source' => $from->getLocale_Language(),
                        'target' => $to->getLocale_Language(),
                    ],
                ]
            );
            $data = json_decode($response->getBody()->getContents(), true);
            if (isset($data['translatedText']) && strlen(trim($data['translatedText']))) {
                $result = $data['translatedText'];
            }
        } catch (\Throwable $exception) {
            $result = $text;
        }
        return $result;
    }

    /**
     * @param string $path
     * @return Uri
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function getEndpoint(string $path)
    {
        $uri = new Uri($this->getConfig()->get('translation.provider.libretranslate.host'));
        return $uri->withPath($path);
    }

    /**
     * @return string
     */
    protected function getEndpointTranslate(): string
    {
        return $this->getEndpoint('translate');
    }
}
