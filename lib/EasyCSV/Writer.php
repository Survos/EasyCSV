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
                    case 'array': return self::is_numeric_array($key) ? join("|", $key) : json_encode($key);
                    default: return $key;
                }
            }, $row);

        if ($this->_line == 0) {
            $columns = array_keys($row);
            $columnNames = $this->_codified_fields ? array_map('self::display_to_code', $columns) : $columns;
            $this->_defaults = array_fill_keys($columns, '');
            fputcsv($this->_handle, $columnNames, $this->_delimiter, $this->_enclosure);
        }
        $this->_line++;
        $unexpected = array_diff(array_keys($row), array_keys($this->_defaults));
        if ($unexpected) {
            throw new \Exception(sprintf('Unexpected column%s found in line %d: %s',
                count($unexpected) == 1 ? '' : 's', $this->_line, implode(', ', $unexpected)));
        }
        $result = fputcsv($this->_handle, $output=array_values(array_merge($this->_defaults, $row)), $this->_delimiter, $this->_enclosure);
        return $result;
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

    // copied from Tt.php
###########################
    static function display_to_code( $name, $max_length = 0 ) {
        static $from = array(
            '/[\xc0-\xc5\xe0-\xe5]/',
            '/[\xc6\xe6]/',
            '/[\xc7\xe7]/',
            '/[\xc8-\xcb\xe8-\xeb]/',
            '/[\xcc-\xcf\xec-\xef]/',
            '/[\xd0\xde\xf0\xfe]/',
            '/[\xd1\xf1]/',
            '/[\xd2-\xd6\xd8\xf2-\xf6\xf8]/',
            '/[\xd9-\xdc\xf9-\xfc]/',
            '/[\xdd\xfd\xff]/',
            '/[\xdf]/'
        );
        static $to = array(
            'a',
            'ae',
            'c',
            'e',
            'i',
            'th',
            'n',
            'o',
            'u',
            'y',
            'ss'
        );
        $name = preg_replace($from, $to, $name); # remove accents
        # Lowercase and change non-alphanumerics to underscores:
        $name = preg_replace('/[^a-z0-9]+/', '_', strtolower($name));
        if ($max_length) $name = substr($name, 0, $max_length);
        $name = preg_replace('/_$/', '', $name); # trim final underscore, if any
        $name = preg_replace('/^_/', '', $name); # trim first underscore, if any
        if ($max_length)
        {
            $name = substr($name, 0, $max_length);
        }
        return $name;
    }

    static function is_numeric_array($a) {
        return is_array($a) and array_keys($a) === range(0, count($a) - 1);
    }

}