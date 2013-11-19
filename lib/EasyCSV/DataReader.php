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
        file_put_contents($path = '/tmp/' . md5($csv_text), $csv_text); // hack! How do I create a temporary file
        parent::__construct($path);
    }


}