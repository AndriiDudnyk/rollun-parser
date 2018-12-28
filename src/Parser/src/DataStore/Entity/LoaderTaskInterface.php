<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\DataStore\Entity;

use rollun\parser\DataStore\Page\PageInterface;

interface LoaderTaskInterface extends PageInterface
{
    const STATUS_NEW = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = 2;
    const STATUS_IN_PROCESS = 3;

    const COLUMN_PARSER_NAME = 'parser';
    const COLUMN_CREATED_AT = 'created_at';
    const COLUMN_STATUS = 'status';
    const COLUMN_OPTIONS = 'options';
    const COLUMN_URI = 'uri';

    public function addLoaderTask($parser, $uri, $options = []);

    /**
     * @param $id
     * @param $status
     * @return void
     */
    public function setStatus($id, $status);

    /**
     * @param $fields
     * @return array|iterable
     */
    public function getLoaderTaskByFields($fields);
}
