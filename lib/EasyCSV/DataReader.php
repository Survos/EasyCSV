<?php

namespace EasyCSV;

use EasyCSV\Field;

class DataReader extends Reader
{
    private $_line;
    private $_as_array=false;
    private $_has_error = false;

    public function __construct($csv_text)
    {
        // create a temporary file, then run this through the normal reader
        file_put_contents($path = tempnam(sys_get_temp_dir(), 'import_csv'), $csv_text);
        parent::__construct($path);
    }


}