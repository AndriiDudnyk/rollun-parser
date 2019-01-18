<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\Parser\Parser\Search;

use phpQuery as PhpQuery;
use rollun\parser\Parser\Parser\HtmlParser;
use rollun\parser\Parser\Parser\ParserInterface;
use rollun\parser\Parser\ParserResolver\ParserResolverInterface;

final class EbayMotors extends HtmlParser implements ParserResolverInterface
{
    public const PARSER_NAME = self::class;

    public function parse(string $data): array
    {
        $document = PhpQuery::newDocument($data);
        $itemCards = $document->find('#ListViewInner > li');
        $products = [];

        foreach ($itemCards as $key => $itemCard) {
            $pq = pq($itemCard);

            if ($pq->find('.kand-expansion')->count()) {
                continue;
            }

            $products[$key]['uri'] = $pq->find('.lvtitle a')->attr('href');
            $urlComponents = parse_url($products[$key]['uri']);
            $path = $urlComponents['path'];
            $parts = explode('/', $path);

            $products[$key]['item_id'] = end($parts);
            $products[$key]['imgs'] = $pq->find('.full-width a .img')->attr('src');

            if ($pq->find('.prRange')->count()) {
                $priceRange = $pq->find('.prRange')->text();
                [$from, ,$to] = explode(' ', $priceRange);
                $products[$key]['price'] = trim($from) . '-' . trim($to);
            } else {
                $products[$key]['price'] = trim($pq->find('.lvprice > span')->text());
            }

            $products[$key]['shipping']['cost'] = trim($pq->find('.lvshipping .fee')->text());

            // Filter trash
            $products[$key]['shipping']['cost'] = str_replace(
                [' shipping', '+'],
                '',
                $products[$key]['shipping']['cost']
            );

            $products[$key]['shipping'] = implode(' ', $products[$key]['shipping']);

            $details = $pq->find('.lvdetails li');

            foreach ($details as $detail) {
                $sellerInfo = pq($detail)->text();
                preg_match('/Seller:\s+([\w\W]+)\(.+\)/', $sellerInfo, $matches);

                if ($matches[1]) {
                    $products[$key]['seller'] = $matches[1] ?? '';
                }
            }
            $products[$key]['seller'] = $products[$key]['seller'] ?? '';

            $hotnessText = trim($pq->find('.watch a')->text());

            $products[$key]['date'] = $pq->find('.timeleft .tme span')->text();

            if (stristr($hotnessText, 'Watch')) {
                $products[$key]['watch'] = $hotnessText;
            }

            if (stristr($hotnessText, 'Sold')) {
                $products[$key]['sold'] = $hotnessText;
            }
        }

        $result['products'] = $products;
        $result['nextPage'] = $document->find('#Pagination .pages .curr + a')->attr('href');

        return $result;
    }

    public function getParser($document): ?ParserInterface
    {
        return new self();
    }

    public function canParse(string $data): bool
    {
        $document = PhpQuery::newDocument($data);
        return boolval($document->find('#ListViewInner > li')->count());
    }
}
