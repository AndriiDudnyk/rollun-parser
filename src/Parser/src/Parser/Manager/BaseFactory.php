<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Parser\Manager;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\parser\DataStore\Entity\ParserTaskInterface;
use rollun\parser\Parser\Parser\ParserInterface;

class BaseFactory extends AbstractFactory
{
    const KEY_PARSER = 'parser';

    const KEY_PARSER_TASK_DATASTORE = 'documentDataStore';

    const KEY_PARSE_RESULT_DATASTORE = 'parseResultDataStore';

    const KEY_OPTIONS = 'options';

    protected function createParserManager(
        ContainerInterface $container,
        array $serviceConfig,
        $class
    ): ParserManagerInterface {
        $parser = $this->createParser($container, $serviceConfig);
        $parseResultDataStore = $this->createParseResultDataStore($container, $serviceConfig);
        $documentDataStore = $this->createParserTask($container, $serviceConfig);
        $options = $this->getOptions($serviceConfig);

        return new $class($parser, $parseResultDataStore, $documentDataStore, $options);
    }

    protected function createParser(ContainerInterface $container, array $serviceConfig): ParserInterface
    {
        if (!isset($serviceConfig[static::KEY_PARSER])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_PARSER . "'");
        }

        return $container->get($serviceConfig[static::KEY_PARSER]);
    }

    protected function getOptions(array $serviceConfig): array
    {
        if (!isset($serviceConfig[self::KEY_OPTIONS])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_OPTIONS . "'");
        }

        return $serviceConfig[self::KEY_OPTIONS];
    }

    protected function createParseResultDataStore(
        ContainerInterface $container,
        array $serviceConfig
    ): DataStoresInterface {
        if (!isset($serviceConfig[static::KEY_PARSE_RESULT_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_PARSE_RESULT_DATASTORE . "'");
        }

        return $container->get($serviceConfig[static::KEY_PARSE_RESULT_DATASTORE]);
    }

    protected function createParserTask(
        ContainerInterface $container,
        array $serviceConfig
    ): ParserTaskInterface {
        if (!isset($serviceConfig[static::KEY_PARSER_TASK_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_PARSER_TASK_DATASTORE . "'");
        }

        return $container->get($serviceConfig[static::KEY_PARSER_TASK_DATASTORE]);
    }
}
