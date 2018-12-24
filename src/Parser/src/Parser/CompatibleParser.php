<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Parser;

class CompatibleParser extends AbstractParser
{
    public const PARSER_NAME = 'ebayCompatible';

    public function parse(string $data): array
    {
        $data = json_decode($data, true);

        $compatibles = $data['data'];
        $result = [];

        foreach ($compatibles as $compatible) {
            $result[] = [
                'make' => $compatible['Make'][0] ?? '',
                'model' => $compatible['Model'][0] ?? '',
                'submodel' => $compatible['Submodel'][0] ?? '',
                'year' => $compatible['Year'][0] ?? '',
            ];
        }

        return $result;
    }
}
