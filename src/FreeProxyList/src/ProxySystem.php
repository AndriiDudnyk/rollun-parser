<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\FreeProxyList;

use Psr\Log\LoggerInterface;
use rollun\datastore\Rql\RqlQuery;
use rollun\dic\InsideConstruct;
use rollun\parser\DataStore\Entity\LoaderTaskInterface;
use rollun\service\Parser\FreeProxyList\DataStore\Entity\ProxyInterface;
use rollun\service\Parser\FreeProxyList\Parser\Parser\HomePage;
use Xiag\Rql\Parser\Node\AbstractQueryNode;
use Xiag\Rql\Parser\Node\LimitNode;
use Xiag\Rql\Parser\Node\Query\LogicOperator\AndNode;
use Xiag\Rql\Parser\Node\Query\LogicOperator\NotNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\LtNode;
use Xiag\Rql\Parser\Node\SortNode;

class ProxySystem
{
    protected $proxy;

    /** @var LoggerInterface */
    protected $logger;

    protected $loaderTask;

    protected $uri;

    protected $loaderTaskOptions;

    const BETTER_OLD_PROXY = 1;
    const BETTER_NEW_PROXY = 1;
    const WORSE_NEW_PROXY = 1;
    const WORSE_OLD_PROXY = 1;
    const NEW_PROXY = 1;
    const FAILED_PROXY = 1;

    // How many seconds proxy should execute to decide that it is a good one
    const TIME_LIMIT = 2;

    public function __construct(
        ProxyInterface $proxy,
        LoaderTaskInterface $loaderTask,
        string $uri,
        array $loaderTaskOptions,
        LoggerInterface $logger = null
    ) {
        $this->proxy = $proxy;
        $this->loaderTask = $loaderTask;
        $this->loaderTaskOptions = $loaderTaskOptions;
        $this->uri = $uri;
        InsideConstruct::setConstructParams(['logger' => LoggerInterface::class]);
    }

    /**
     * @param bool $createTaskIfNotExist
     * @return string|null
     */
    public function get($createTaskIfNotExist = true): ?string
    {
        $proxy = $this->getProxy();

        if ($proxy && $createTaskIfNotExist) {
            $taskId = $this->loaderTask->addLoaderTask(HomePage::PARSER_NAME, $this->uri, $this->loaderTaskOptions);
            $this->logger->info("Create new task #{$taskId}");

            return null;
        }

        return $proxy;
    }

    public function upgrade(string $uri)
    {
        $proxy = $this->proxy->getRecordByFields(['uri' => $uri]);

        if (!$proxy) {
            return;
        }

        $level = intval($proxy[ProxyInterface::COLUMN_LEVEL]);
        $level = $level < 10 ? $level + 1 : $level;

        $this->proxy->update([
            $this->proxy->getIdentifier() => $proxy[$this->proxy->getIdentifier()],
            ProxyInterface::COLUMN_LEVEL => $level,
        ]);
    }

    public function downgrade(string $uri)
    {
        $proxy = $this->proxy->getRecordByFields(['uri' => $uri]);

        if (!$proxy) {
            return;
        }

        $level = intval($proxy[ProxyInterface::COLUMN_LEVEL]);
        $level = $level > 0 ? $level - 1 : $level;

        $this->proxy->update([
            $this->proxy->getIdentifier() => $proxy[$this->proxy->getIdentifier()],
            ProxyInterface::COLUMN_LEVEL => $level,
        ]);
    }

    public function changeLevel(string $uri, string $time)
    {
        if ($time < self::TIME_LIMIT) {
            $this->upgrade($uri);
        } else {
            $this->downgrade($uri);
        }
    }

    public function failed($uri)
    {
        $proxy = $this->proxy->getRecordByFields(['uri' => $uri]);

        if (!$proxy) {
            return;
        }

        $this->proxy->update([
            $this->proxy->getIdentifier() => $proxy[$this->proxy->getIdentifier()],
            ProxyInterface::COLUMN_LEVEL => ProxyInterface::MIN_LEVEL,
        ]);
    }

    protected function getProxy()
    {
        $aggregate = [];
        $percentages = $this->getProxyPercentage();

        foreach ($percentages as $percentage) {
            $aggregate[] = $percentage['name'];
        }

        $randomPercentage = $percentages[array_rand($aggregate)];

        switch ($randomPercentage) {
            case 'BETTER_OLD_PROXY':
                $sortNode = new SortNode([ProxyInterface::COLUMN_UPDATED_AT => SortNode::SORT_ASC]);
                $proxy = $this->getBetterProxies($sortNode);
                break;
            case 'BETTER_NEW_PROXY':
                $sortNode = new SortNode([ProxyInterface::COLUMN_UPDATED_AT => SortNode::SORT_DESC]);
                $proxy = $this->getBetterProxies($sortNode);
                break;
            case 'WORSE_OLD_PROXY':
                $sortNode = new SortNode([ProxyInterface::COLUMN_UPDATED_AT => SortNode::SORT_ASC]);
                $proxy = $this->getWorseProxies($sortNode);
                break;
            case 'WORSE_NEW_PROXY':
                $sortNode = new SortNode([ProxyInterface::COLUMN_UPDATED_AT => SortNode::SORT_DESC]);
                $proxy = $this->getWorseProxies($sortNode);
                break;
            case 'NEW_PROXY':
                $proxy = $this->getNewProxy();
                break;
            case 'FAILED_PROXY':
                $proxy = $this->getFailedProxy();
                break;
            default:
                throw new \InvalidArgumentException("Cannot define random proxy using percentage {$randomPercentage}");
                break;
        }

        if (!$proxy) {
            return $this->getProxyByQueryNode();
        }

        return $proxy;
    }

    protected function getProxyPercentage()
    {
        return [
            ['name' => 'BETTER_OLD_PROXY', 'percent' => self::BETTER_OLD_PROXY],
            ['name' => 'BETTER_NEW_PROXY', 'percent' => self::BETTER_NEW_PROXY],
            ['name' => 'WORSE_OLD_PROXY', 'percent' => self::WORSE_OLD_PROXY],
            ['name' => 'WORSE_NEW_PROXY', 'percent' => self::WORSE_NEW_PROXY],
            ['name' => 'NEW_PROXY', 'percent' => self::NEW_PROXY],
            ['name' => 'FAILED_PROXY', 'percent' => self::FAILED_PROXY],
        ];
    }

    protected function getProxyByQueryNode(AbstractQueryNode $queryNode = null, SortNode $sortNode = null)
    {
        $query = new RqlQuery();

        if ($queryNode) {
            $query->setQuery($queryNode);
        }

        if ($sortNode) {
            $query->setSort($sortNode);
        }

        $query->setLimit(new LimitNode(1));

        $proxies = $this->proxy->query($query);

        if (count($proxies)) {
            return current($proxies);
        }

        return null;
    }

    protected function getNewProxy()
    {
        return $this->getProxyByQueryNode(
            new EqNode(ProxyInterface::COLUMN_LEVEL, ProxyInterface::MIN_LEVEL),
            new SortNode([ProxyInterface::COLUMN_UPDATED_AT => SortNode::SORT_ASC])
        );
    }

    protected function getFailedProxy()
    {
        return $this->getProxyByQueryNode(
            new EqNode(ProxyInterface::COLUMN_LEVEL, ProxyInterface::FAIL_LEVEL),
            new SortNode([ProxyInterface::COLUMN_UPDATED_AT => SortNode::SORT_ASC])
        );
    }

    protected function getWorseProxies($sortNode)
    {
        $averageLevel = intval((ProxyInterface::MAX_LEVEL + ProxyInterface::MIN_LEVEL) / 2);

        return $this->getProxyByQueryNode(new AndNode([
            new LtNode(ProxyInterface::COLUMN_LEVEL, $averageLevel),
            new NotNode([new EqNode(ProxyInterface::COLUMN_LEVEL, ProxyInterface::MIN_LEVEL)]),
            new NotNode([new EqNode(ProxyInterface::COLUMN_LEVEL, ProxyInterface::FAIL_LEVEL)]),
        ]), $sortNode);
    }

    protected function getBetterProxies($sortNode)
    {
        $averageLevel = (ProxyInterface::MAX_LEVEL + ProxyInterface::MIN_LEVEL) / 2;

        return $this->getProxyByQueryNode(new LtNode(ProxyInterface::COLUMN_LEVEL, $averageLevel), $sortNode);
    }
}
