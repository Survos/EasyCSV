<?php

namespace EasyCSV;

abstract class AbstractBase
{
    protected $_handle;
    protected $_delimiter = ',';
    protected $_enclosure = '"';
    protected $_force_utf8 = false;
    protected $_codified_fields = false;
    protected $_path;
    protected $_headers;

    public function __construct($path, $mode = 'r+')
    {
        if ( ! file_exists($path)) {
            touch($path);
        }
        $this->_handle = fopen($path, $mode);
        $this->_path = $path;
    }

    public function __destruct()
    {
        if (is_resource($this->_handle)) {
            fclose($this->_handle);
        }
    }

    public function setDelimiter($delimiter)
    {
      $this->_delimiter = $delimiter;
    }

    public function setForceUtf8($force_utf8=true)
    {
      $this->_force_utf8 = $force_utf8;
    }

    public function setCodifiedFields($bool=true)
    {
      $this->_codified_fields = $bool;
    }

    public function getForceUtf8($force_utf8=true)
    {
      return $this->_force_utf8;
    }

    public function getPath() {
      return $this->_path;
    }

    // set the header when the first line doesn't have the field names.
    public function setHeader($header)
    {
        $this->_headers = $header;
        $this->createFields($header);
        $this->_header_count = count($header);

    }
}