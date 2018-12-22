<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Parser;

use phpQuery as PhpQuery;

class SearchParser extends AbstractParser
{
    public const PARSER_NAME = 'ebaySearch';

    /**
     * @param string $html
     * @return array
     */
    public function parse(string $html): array
    {
        $document = PhpQuery::newDocument($html);
        $itemCards = $document->find('.s-item__wrapper');
        $product = [];

        foreach ($itemCards as $key => $itemCard) {
            $pq = pq($itemCard);

            $product[$key]['url'] = $pq->find('.s-item__link')->attr('href');
            $thisItemUrlPath = parse_url($product[$key]['url'])['path'];

            $product[$key]['item_id'] = end(explode('/', $thisItemUrlPath));
            $product[$key]['img'] = $pq->find('.s-item__image-img')->attr('src');
            $product[$key]['price'] = $pq->find('span.s-item__price')->text();
            $product[$key]['shipping']['cost'] = $pq->find('.s-item__shipping')->text();

            // Filter trash
            $product[$key]['shipping']['cost'] = str_replace(
                [' shipping', '+'],
                '',
                $product[$key]['shipping']['cost']
            );

            $product[$key]['shipping'] = implode(' ', $product[$key]['shipping']);

            $hotnessText = $pq->find('.s-item__hotness>.NEGATIVE')->text();

            if (stristr($hotnessText, 'Watching')) {
                $product[$key]['watching'] = $hotnessText;
            }

            if (stristr($hotnessText, 'Sold')) {
                $product[$key]['sold'] = $hotnessText;
            }
        }

        return $product;
    }
}
