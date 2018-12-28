<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\DataStore\Entity;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class ParserTaskFactory
{
    const KEY_PARSER_TASK_DATASTORE = 'parserTaskDataStore';

    const KEY_STORE_DIR = 'storeDir';

    public function __invoke(ContainerInterface $container)
    {
        $serviceConfig = $container->get('config')[self::class] ?? [];

        if (!isset($serviceConfig[self::KEY_PARSER_TASK_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_PARSER_TASK_DATASTORE . "'");
        }

        if (!isset($serviceConfig[self::KEY_STORE_DIR])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_STORE_DIR . "'");
        }

        $storeDir = $serviceConfig[self::KEY_STORE_DIR];
        $downloadDataStore = $container->get($serviceConfig[self::KEY_PARSER_TASK_DATASTORE]);

        return new ParserTask($downloadDataStore, $storeDir);
    }
}
