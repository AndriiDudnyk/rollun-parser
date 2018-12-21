<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Parser;

use phpQuery as PhpQuery;

class SearchParser implements ParserInterface
{
    /**
     * @param string $html
     * @return array
     */
    public function parse(string $html): array
    {
        $document = PhpQuery::newDocument($html);
        $itemCards = $document->find('.s-item__wrapper');
        $product = [];

        foreach ($itemCards as $itemCardKey => $itemCard) {
            $pq = pq($itemCard);

            $product['url'] = $pq->find('.s-item__link')->attr('href');
            $thisItemUrlPath = parse_url($product['url'])['path'];

            $product['itemId'] = end(explode('/', $thisItemUrlPath));
            $product['urlOfImg'] = $pq->find('.s-item__image-img')->attr('src');
            $product['price'] = $pq->find('span.s-item__price')->text();
            $product['shipping']['cost'] = $pq->find('.s-item__shipping')->text();

            // Filter trash
            $product['shipping']['cost'] = str_replace(
                [' shipping', '+'],
                '',
                $product['shipping']['cost']
            );

            $product['shipping'] = implode(' ', $product['shipping']);

            $hotnessText = $pq->find('.s-item__hotness>.NEGATIVE')->text();

            if (stristr($hotnessText, 'Watching')) {
                $product['watching'] = $hotnessText;
            }

            if (stristr($hotnessText, 'Sold')) {
                $product['sold'] = $hotnessText;
            }
        }

        return $product;
    }
}
