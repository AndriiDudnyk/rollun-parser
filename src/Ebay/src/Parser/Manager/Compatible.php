<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\Parser\Manager;

use InvalidArgumentException;
use rollun\parser\DataStore\Entity\LoaderTaskInterface;
use rollun\parser\DataStore\Entity\ParserTaskInterface;
use rollun\service\Parser\Ebay\DataStore\Entity\Interfaces\CompatibleInterface;
use rollun\service\Parser\Ebay\Parser\Parser\Compatible as CompatibleParser;
use Psr\Log\LoggerInterface;
use rollun\parser\Parser\Manager\Base as BaseParserManager;

class Compatible extends BaseParserManager
{
    protected $loaderTask;

    public function __construct(
        CompatibleParser $parser,
        CompatibleInterface $entity,
        ParserTaskInterface $parserTask,
        LoaderTaskInterface $loaderTask,
        array $options,
        LoggerInterface $logger = null
    ) {
        parent::__construct($parser, $entity, $parserTask, $options);
        $this->loaderTask = $loaderTask;
    }

    protected function processResult(array $data, $parserTask): array
    {
        if (!$itemId = $this->options['item_id'] ?? null) {
            throw new InvalidArgumentException("Invalid option 'item_id'");
        }

        if (!$compatibleUri = $this->options['compatible_uri'] ?? null) {
            throw new InvalidArgumentException("Invalid option 'compatible_uri'");
        }

        $compatibles = $data['compatibles'];
        foreach ($compatibles as $key => $record) {
            $compatibles[$key]['item_id'] = $itemId;
            $compatibles[$key]['id'] = $this->createCompatibleId($itemId, $record);
        }

        $currentPageNo = $data['currentPageNo'];
        $totalPageCount = $data['totalPageCount'];

        if ($currentPageNo < $totalPageCount) {
            $newPageNo = $currentPageNo + 1;
            $compatibleUri = str_replace("page={$currentPageNo}", "page={$newPageNo}", $compatibleUri);
            $this->loaderTask->addLoaderTask(CompatibleParser::PARSER_NAME, $compatibleUri, [
                self::KEY_OPTIONS => [
                    'item_id' => $itemId,
                    'compatible_uri' => $compatibleUri,
                ]
            ]);
        }

        return $compatibles;
    }

    protected function saveResult(array $records)
    {
        foreach ($records as $record) {
            $this->entity->create($record, true);
        }
    }

    public function __sleep()
    {
        $properties = parent::__sleep();

        return array_merge($properties, ['loaderTask']);
    }

    protected function createCompatibleId($itemId, $record)
    {
        return $itemId
            . '-' . $record['make']
            . '-' . $record['model']
            . '-' . $record['submodel']
            . '-' . $record['year'];
    }
}
