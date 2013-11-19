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

        if ($this->getForceUtf8()) {
            $row = array_map(function($key) {
                return mb_check_encoding($key, 'UTF-8') ? $key : utf8_encode($key);
            }, $row);
        }

        // fix the DateTime object and arrays
            $row = array_map(function($key) {
                switch (gettype($key)) {
                    case 'DateTime': return $key->format('c');
                    case 'array': return \Survos\Lib\tt::is_numeric_array($key) ? join("|", $key) : json_encode($key);
                    default: return $key;
                }
            }, $row);

        if ($this->_line == 0) {
            $columns = array_keys($row);
            $this->_defaults = array_fill_keys($columns, '');
            fputcsv($this->_handle, $columns, $this->_delimiter, $this->_enclosure);
        }
        $this->_line++;
        $unexpected = array_diff(array_keys($row), array_keys($this->_defaults));
        if ($unexpected) {
            throw new \Exception(sprintf('Unexpected column%s found in line %d: %s',
                count($unexpected) == 1 ? '' : 's', $this->_line, implode(', ', $unexpected)));
        }
        return fputcsv($this->_handle, array_merge($this->_defaults, $row), $this->_delimiter, $this->_enclosure);
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