<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Loader;

interface LoaderInterface
{
    /**
     * Load html from target
     *
     * @param string $uri
     * @return string
     */
    public function load(string $uri): string;
}
