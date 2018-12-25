<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore\Parser;

use Parser\Loader\Loader;
use Parser\Parser\ParserInterface;
use rollun\datastore\Rql\Node\AggregateFunctionNode;
use Xiag\Rql\Parser\Query;

class BaseStorage extends AbstractStorage
{
    protected $loader;

    protected $parser;

    public function __construct(Loader $loader, ParserInterface $parser)
    {
        $this->loader = $loader;
        $this->parser = $parser;
    }

    protected function executeQuery($params)
    {
        $html = $this->loader->load($params['uri']);
        $records = $this->parser->parse($html);

        return [$records];
    }

    public function query(Query $query)
    {
        if ($query->getSelect()) {
            foreach ($query->getSelect()->getFields() as $selectNode) {
                if ($selectNode instanceof AggregateFunctionNode) {
                    return [$selectNode->__toString() => 1];
                }
            }
        }

        $params = $this->parseQuery($query);
        $this->validate($params, ['uri']);

        return $this->executeQuery($params);
    }
}
