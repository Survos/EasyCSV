<?php

namespace EasyCSV;

class DataReader extends Reader
{
    /** @var string */
    private $tempFile;

    /**
     * DataReader constructor.
     * @param string $csv_text
     */
    public function __construct($csv_text)
    {
        // create a temporary file, then run this through the normal reader
        $this->tempFile = tempnam(sys_get_temp_dir(), 'import_csv');
        file_put_contents($this->tempFile, $csv_text);
        parent::__construct($this->tempFile);
    }

    public function __destruct()
    {
        @unlink($this->tempFile);
    }
}
