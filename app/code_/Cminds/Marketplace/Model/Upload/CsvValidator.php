<?php

namespace Cminds\Marketplace\Model\Upload;

class CsvValidator
{
    private $allowedMimes = ['application/vnd.ms-excel','text/plain','text/csv','text/tsv'];

    /**
     * Csv file type validator.
     *
     * @param string $type
     *
     * @return bool
     */
    public function validateFileType(string $type)
    {
        if (!$type) {
            return false;
        }

        if (in_array($type, $this->allowedMimes)) {
            return true;
        }

        return false;
    }
}
