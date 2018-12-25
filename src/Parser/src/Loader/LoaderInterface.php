<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Loader;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use RuntimeException;

interface LoaderInterface extends ClientInterface
{
    /**
     * @param string $uri
     * @return string
     * @throws ClientExceptionInterface
     * @throws RuntimeException
     */
    public function load(string $uri): string;

    /**
     * @param array $options
     * @return mixed
     */
    public function setOptions($options);
}
