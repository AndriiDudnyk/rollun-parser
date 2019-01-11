<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\DataStore\Entity;

use rollun\datastore\DataStore\Interfaces\DataStoresInterface;

interface ParserTaskInterface extends DataStoresInterface
{
    const STATUS_NEW = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = 2;
    const STATUS_NOT_PARSED = 3;
    const STATUS_IN_PROCESS = 4;

    const COLUMN_PARSER_NAME = 'parser';
    const COLUMN_CREATED_AT = 'created_at';
    const COLUMN_UPDATED_AT = 'updated_at';
    const COLUMN_STATUS = 'status';
    const COLUMN_OPTIONS = 'options';
    const COLUMN_FILE = 'file';

    const COLUMN_ABSTRACT_DOCUMENT = 'document';

    /**
     * Add new task with status self::STATUS_NEW
     *
     * @param $parser
     * @param $document
     * @param array $options
     * @return mixed
     */
    public function addParserTask($parser, $document, $options = []);

    /**
     * @param $id
     * @param $status
     * @return void
     */
    public function setStatus($id, $status);

    /**
     * @param $file
     * @return mixed
     */
    public function findByFile($file);

    /**
     * @param $fields
     * @return array|iterable
     */
    public function getParserTaskByFields($fields);
}
