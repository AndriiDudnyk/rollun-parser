<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Loader\Loader;

use Psr\Http\Message\ResponseInterface;
use rollun\parser\DataStore\Entity\LoaderTaskInterface;

class LoaderException extends \Exception
{
    public static function createProxyRunOutException($createTaskIfNoExist)
    {
        $addMessage = $createTaskIfNoExist ? ', and add new task for proxy parser' : '';
        throw new self("Unused proxies run out" . $addMessage, 0);
    }

    public static function createCannotLoadException($uri, ResponseInterface $response)
    {
        throw new self(
            "Can't load html from '$uri'. Reason: {$response->getReasonPhrase()}",
            LoaderTaskInterface::STATUS_FAILED
        );
    }
}
