<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Loader;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Zend\Diactoros\ServerRequestFactory;

class SearchLoaderHelperFactory
{
    const KEY_CLIENT = 'client';
    const KEY_REDIRECT_URI = 'redirectUri';

    public function __invoke(ContainerInterface $container)
    {
        $serviceConfig = $container->get('config')[self::class] ?? [];

        if (!isset($serviceConfig[self::KEY_CLIENT])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_CLIENT . "'");
        }

        if (!isset($serviceConfig[self::KEY_REDIRECT_URI])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_REDIRECT_URI . "'");
        }

        $requestFactory = new ServerRequestFactory();
        $client = $container->get($serviceConfig[self::KEY_CLIENT]);
        $redirectUri = $serviceConfig[self::KEY_REDIRECT_URI];

        return new SearchLoaderHelper($client, $requestFactory, $redirectUri);
    }
}
