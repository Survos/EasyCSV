<?php

namespace EasyCSV;

abstract class AbstractBase
{
    protected $_handle;
    protected $_delimiter = ',';
    protected $_enclosure = '"';
    protected $_force_utf8 = false;
    protected $_unnamed_extra_data_var = false;
    protected $_codified_fields = false;
    protected $_fix_escaped = true;
    protected $_path;
    protected $_headers;

    const FIELDNAME_PRESERVE = 0;
    const FIELDNAME_UNDERSCORE = 1;
    const FIELDNAME_CAMELCAP = 2;
    const FIELDNAME_NO_SYMBOLS = 3;


    public function __construct($path, $mode = 'r+')
    {
        if ( ! file_exists($path)) {
            touch($path);
            if (substr($mode, 0, 1) == 'w') {
                chmod($path, 0775); // make group writable
            }
        }

        $this->setDelimiter($this->detectDelimiter($path));
        $this->_path = $path;
        $this->_handle = fopen($path, $mode);

    }

    protected function detectDelimiter($fn) {
        $handle = @fopen($fn, "r");
        $default = ',';
        if ($handle) {
            $line=fgets($handle, 4096);
            fclose($handle);

            foreach (array("\t", '|') as $candidate) {
                if (substr_count($line, "\t")) {
                    return  $candidate;
                }
            }
            //.. and so on
        }
        //return default delimiter
        return $default;
    }

    function getBasename()
    {
        return basename($this->_path);
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

    public function setEnclosure($enclosure='')
    {
      $this->_enclosure = $enclosure;
    }

    public function setForceUtf8($force_utf8=true)
    {
      $this->_force_utf8 = $force_utf8;
    }

        public function setUnnamedExtraDataVar($v)
    {
        $this->_unnamed_extra_data_var = $v;
    }

    public function setCodifiedFields($setting=null)
    {
      if (is_null($setting) || $setting === true)
      {
          $setting = AbstractBase::FIELDNAME_UNDERSCORE;
      }
      $this->_codified_fields = $setting;
    }

    public function setFixEscaped($bool=true)
    {
      $this->_fix_escaped = $bool;
    }

    public function getForceUtf8($force_utf8=true)
    {
      return $this->_force_utf8;
    }

    public function getPath() {
      return $this->_path;
    }

    public function getFixEscaped() {
      return $this->_fix_escaped;
    }

    // set the header when the first line doesn't have the field names.
    public function setHeader($header)
    {
        $this->_headers = $header;
        $this->createFields($header);
        $this->_header_count = count($header);
    }

    public function getLineCount()
    {
        $lines_command = sprintf('wc -l %s', $this->_path);
        $lines = system($lines_command);
        return $lines;
    }


}