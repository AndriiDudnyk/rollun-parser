<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Loader\Loader;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use rollun\parser\Loader\Loader\ResponseValidator\StatusOk;
use Zend\Diactoros\ServerRequestFactory;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class LoaderAbstractFactory implements AbstractFactoryInterface
{
    const KEY_PROXY_SYSTEM = 'proxySystem';
    const KEY_RESPONSE_VALIDATOR = 'responseValidator';
    const KEY_OPTIONS = 'options';

    const DEF_RESPONSE_VALIDATOR = StatusOk::class;

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $serviceConfig = $container->get('config')[self::class][$requestedName];

        if (!isset($serviceConfig[self::KEY_PROXY_SYSTEM])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_PROXY_SYSTEM . "'");
        }

        $requestFactory = new ServerRequestFactory();
        $options = $serviceConfig[self::KEY_OPTIONS] ?? [];
        $proxySystem = $container->get($serviceConfig[self::KEY_PROXY_SYSTEM]);
        $responseValidatorService = $serviceConfig[self::KEY_RESPONSE_VALIDATOR] ?? self::DEF_RESPONSE_VALIDATOR;
        $responseValidator = $container->get($responseValidatorService);

        return new Base($proxySystem, $requestFactory, $responseValidator, $options);
    }

    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return $container->get('config')[self::class][$requestedName] ?? null;
    }
}
