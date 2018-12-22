<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Loader;

interface LoaderInterface
{
    /**
     * @param string $uri
     * @return string
     */
    public function load(string $uri): string;

    /**
     * @param array $options
     * @return mixed
     */
    public function setOptions($options);
}
