<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Manager\Factory;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Parser\DataStore\DocumentDataStoreInterface;
use Parser\Manager\BaseParserManager;
use Parser\Parser\ParserInterface;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;

class BaseParserManagerFactory extends AbstractParserManagerFactory
{
    const KEY_PARSER = 'parser';

    const KEY_DOCUMENT_DATASTORE = 'documentDataStore';

    const KEY_PARSE_RESULT_DATASTORE = 'parseResultDataStore';

    const KEY_OPTIONS = 'options';

    protected function createParserManager(ContainerInterface $container, array $serviceConfig): BaseParserManager
    {
        $parser = $this->createParser($container, $serviceConfig);
        $parseResultDataStore = $this->createParseResultDataStore($container, $serviceConfig);
        $documentDataStore = $this->createDocumentDataStore($container, $serviceConfig);
        $options = $this->getOptions($serviceConfig);

        return new BaseParserManager($parser, $parseResultDataStore, $documentDataStore, $options);
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

    protected function createDocumentDataStore(
        ContainerInterface $container,
        array $serviceConfig
    ): DocumentDataStoreInterface {
        if (!isset($serviceConfig[static::KEY_DOCUMENT_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_DOCUMENT_DATASTORE . "'");
        }

        return $container->get($serviceConfig[static::KEY_DOCUMENT_DATASTORE]);
    }
}
