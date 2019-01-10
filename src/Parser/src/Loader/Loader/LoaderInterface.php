<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Loader\Loader;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use RuntimeException;

interface LoaderInterface extends ClientInterface
{
    const DEF_MAX_ATTEMPTS = 1;
    const DEF_USER_AGENT_OS = 'linux';
    const DEF_CREATE_TASK_IF_NO_PROXIES = false;

    // Options
    const MAX_ATTEMPTS_OPTION = 'maxAttempts'; // int
    const USE_PROXY_OPTION = 'useProxy'; // bool
    const FAKE_USER_AGENT_OPTION = 'fakeUserAgent'; // bool
    const FAKE_USER_AGENT_OS_OPTION = 'fakeUserAgentOS'; // 'linux' or 'windows' or 'mac'
    const COOKIES_OPTION = 'cookies'; // array
    const COOKIE_DOMAIN_OPTION = 'cookieDomain'; // string
    const ALLOW_REDIRECT_OPTION = 'allowRedirect'; // bool
    const CONNECTION_TIMEOUT_OPTION = 'connectionTimeout'; // int
    const CREATE_TASK_IF_NO_PROXIES_OPTION = 'createTaskIfNoProxy';

    /**
     * @param string $uri
     * @return string
     * @throws ClientExceptionInterface
     * @throws LoaderException
     */
    public function load(string $uri): string;

    /**
     * @param array $options
     * @return mixed
     */
    public function setOptions($options);
}
