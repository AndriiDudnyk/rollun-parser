<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser;

use InvalidArgumentException;

class SaveStrategy
{
    protected $fileName;

    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }

    public function save(array $records, $rewrite = true)
    {
        if (!file_exists($this->fileName)) {
            throw new InvalidArgumentException("File '$this->fileName' does not exist");
        }

        if ($rewrite) {
            $fp = fopen($this->fileName, 'w');
        } else {
            $fp = fopen($this->fileName, 'a');
        }

        foreach ($records as $record) {
            fputcsv($fp, $record);
        }

        fclose($fp);
    }
}
