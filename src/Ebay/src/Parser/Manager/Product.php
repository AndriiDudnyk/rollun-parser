<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\Parser\Manager;

use InvalidArgumentException;
use rollun\parser\DataStore\Entity\LoaderTaskInterface;
use rollun\parser\DataStore\Entity\ParserTaskInterface;
use rollun\service\Parser\Ebay\DataStore\Entity\Interfaces\ProductInterface;
use rollun\service\Parser\Ebay\Parser\Parser\Product as ProductParser;
use rollun\service\Parser\Ebay\Parser\Parser\Compatible as CompatibleParser;
use Psr\Log\LoggerInterface;
use rollun\parser\Parser\Manager\Base as BaseParserManager;

class Product extends BaseParserManager
{
    protected $loaderTask;

    public function __construct(
        ProductParser $parser,
        ProductInterface $entity,
        ParserTaskInterface $parserTask,
        LoaderTaskInterface $loaderTask,
        array $options,
        LoggerInterface $logger = null
    ) {
        parent::__construct($parser, $entity, $parserTask, $options);
        $this->loaderTask = $loaderTask;
    }

    protected function processResult(array $record, $parserTask): array
    {
        if (!$itemId = $this->options['item_id'] ?? null) {
            throw new InvalidArgumentException("Invalid option 'item_id'");
        }

        $product = $this->entity->read($itemId);
        $record['item_id'] = $itemId;

        if (!$product) {
            $aheadOfCount = $this->options['aheadOfCount'] ?? 0;
            $this->parserTask->update([
                'id' => $parserTask['id'],
                ParserTaskInterface::COLUMN_OPTIONS => array_merge($this->options, ['aheadOfCount' => ++$aheadOfCount]),
                ParserTaskInterface::COLUMN_STATUS => ParserTaskInterface::STATUS_NEW
            ]);

            if ($aheadOfCount >= 100) {
                throw new InvalidArgumentException("Product with id #{$itemId} not found: {$aheadOfCount}");
            }
        }

        $record['ebay_id'] = empty($product['ebay_id']) ? $record['ebay_id'] : $product['ebay_id'];

        $this->createNewTasks($record);
        return $record;
    }

    protected function createNewTasks($record)
    {
        if ($record['ebay_id']) {
            $compatibleUri = rtrim($this->options['compatibleUriEbayId']) . $record['ebay_id'];
        } else {
            $compatibleUri = rtrim($this->options['compatibleUriItemId']) . $record['item_id'];
        }

        $this->loaderTask->addLoaderTask(CompatibleParser::PARSER_NAME, $compatibleUri, [
            self::KEY_OPTIONS => [
                'item_id' => $record['item_id'],
                'compatible_uri' => $compatibleUri,
            ]
        ]);
    }

    protected function saveResult(array $record)
    {
        $record['id'] = $record['item_id'];
        $record['ebay_id'] = $record['ebay_id'] ?? '';
        unset($record['item_id']);
        $this->entity->update($record);
    }

    public function __sleep()
    {
        $properties = parent::__sleep();

        return array_merge($properties, ['loaderTask']);
    }
}
