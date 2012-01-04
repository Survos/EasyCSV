<?php

namespace EasyCSV;

class Reader extends AbstractBase
{
    private $_headers;
    private $_line;

    public function __construct($path, $mode = 'r+')
    {
        parent::__construct($path, $mode);
        $this->_headers = $this->getRow();
        $this->_line    = 0;
    }

    public function getRow()
    {
        if (($row = fgetcsv($this->_handle, 4096, $this->_delimiter, $this->_enclosure)) !== false) {
            $this->_line++;
            try
            {
              $ret = $this->_headers ? array_combine($this->_headers, $row) : $row;
            } catch (\ErrorException $e) {
              $ret = $row;
            }
            return $ret;
        } else {
            return false;
        }
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
}