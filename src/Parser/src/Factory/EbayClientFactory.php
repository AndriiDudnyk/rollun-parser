<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Factory;

use Exception;
use Parser\EbayClient;
use Psr\Container\ContainerInterface;

class EbayClientFactory
{
    const KEY_PROXIES = 'proxies';

    /**
     * @param ContainerInterface $container
     * @return EbayClient
     * @throws Exception
     */
    public function __invoke(ContainerInterface $container)
    {

    }
}
