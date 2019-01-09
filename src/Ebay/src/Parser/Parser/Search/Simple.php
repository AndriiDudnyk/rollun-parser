<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\Parser\Parser\Search;

use phpQuery as PhpQuery;
use rollun\parser\Parser\Parser\HtmlParser;

class Simple extends HtmlParser
{
    public const PARSER_NAME = 'ebaySimpleSearch';

    /**
     * @param string $data
     * @return array
     */
    public function parse(string $data): array
    {
        $document = PhpQuery::newDocument($data);
        $itemCards = $document->find('.s-item__wrapper');
        $products = [];

        foreach ($itemCards as $key => $itemCard) {
            $pq = pq($itemCard);

            $products[$key]['uri'] = $pq->find('.s-item__link')->attr('href');
            $urlComponents = parse_url($products[$key]['uri']);
            $path = $urlComponents['path'];
            $parts = explode('/', $path);

            $products[$key]['item_id'] = end($parts);
            $products[$key]['imgs'] = $pq->find('.s-item__image-img')->attr('src');
            $products[$key]['price'] = $pq->find('span.s-item__price')->text();
            $products[$key]['shipping']['cost'] = $pq->find('.s-item__shipping')->text();

            // Filter trash
            $products[$key]['shipping']['cost'] = str_replace(
                [' shipping', '+'],
                '',
                $products[$key]['shipping']['cost']
            );

            $products[$key]['shipping'] = implode(' ', $products[$key]['shipping']);

            $sellerInfo = $pq->find('.s-item__seller-info-text')->text();
            [,$sellerId,,,] = explode(' ', $sellerInfo);
            $products[$key]['seller'] = $sellerId;

            $hotnessText = $pq->find('.s-item__hotness>.NEGATIVE')->text();

            if (stristr($hotnessText, 'Watching')) {
                $products[$key]['watching'] = $hotnessText;
            }

            if (stristr($hotnessText, 'Sold')) {
                $products[$key]['sold'] = $hotnessText;
            }
        }

        return $products;
    }
}
