<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Loader;

use Exception;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;

class ClientException extends Exception implements RequestExceptionInterface
{
    protected $request;

    public function __construct(string $message, RequestInterface $request)
    {
        $this->request = $request;
        parent::__construct($message, 0);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
