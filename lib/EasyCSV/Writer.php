<?php

namespace EasyCSV;

class Writer extends AbstractBase
{
    public function __construct($path, $mode = 'w+')
    {
        parent::__construct($path, $mode);
        $this->_line    = 0;
    }

    public function writeRow($row)
    {
        if (is_string($row)) {
            $row = explode(',', $row);
            $row = array_map('trim', $row);
        }
        $row = array_map(function($key) {
            return mb_check_encoding($key, 'UTF-8') ? $key : utf8_encode($key);
        }, $row);
        if ($this->_line == 0) {
          fputcsv($this->_handle, array_keys($row), $this->_delimiter, $this->_enclosure);
        }
        $this->_line++;
        return fputcsv($this->_handle, $row, $this->_delimiter, $this->_enclosure);
    }

    public function writeFromArray(array $array)
    {
        foreach ($array as $key => $value) {
            $this->writeRow($value);
        }
    }

    public function getLineCount()
    {
        return $this->_line;
    }
}