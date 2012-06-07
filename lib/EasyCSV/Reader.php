<?php

namespace EasyCSV;

class Reader extends AbstractBase
{
    private $_headers;
    private $_line;
    private $_as_array=false;
    private $_has_error = false;

    public function __construct($path, $mode = 'r+')
    {
        parent::__construct($path, $mode);
        $this->_line    = 0;
    }

    public function getRow()
    {
        $this->_has_error = false;
        if (($row = fgetcsv($this->_handle, 4096, $this->_delimiter, $this->_enclosure)) !== false) {
            if ($this->getForceUtf8()) {
              $row = array_map(function($key) {
                return mb_check_encoding($key, 'UTF-8') ? $key : utf8_encode($key);
              }, $row);
            }

            $this->_line++;
            if ($this->asArray()) {
              return $row;
            } elseif (empty($this->_headers) ) {
              $this->_headers = $row;
              $this->_header_count = count($row);
              return $this->getRow();
            } else {
              if (count($row) <> $this->_header_count) {
                $this->_has_error = true;
                $this->_error = "Bad Data, wrong number of rows";
                // throw new \Exception("Bad Data, wrong number of rows");
                return $row;
              }
              try {
                $ret = array_combine($this->_headers, $row);
              } catch (\ErrorException $e) {
                $ret = $row;
              }
              return $ret;
            }
        } else {
            return false;
        }
    }

    public function hasError() {
      return $this->_has_error;
    }

    public function getError() {
      return $this->_error;
    }

    public function getAll()
    {
        $data = array();
        while ($row = $this->getRow()) {
            $data[] = $row;
        }
        return $data;
    }

    public function getLineNumber()
    {
        return $this->_line;
    }

    public function setAsArray($as_array) {
      $this->_as_array = $as_array;
    }

    public function asArray() {
      return $this->_as_array;
    }

}