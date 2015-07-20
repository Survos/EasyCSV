<?php

namespace EasyCSV;

use EasyCSV\Field;

class Reader extends AbstractBase
{
    private $_line;
    private $_as_array=false;
    private $_has_error = false;
    /**
     * @var bool
     */
    private $headersInFirstRow = true;

    /**
     * @var array|bool
     */
    private $headers = false;

    /**
     * @var
     */
    private $init;

    /**
     * @var bool|int
     */
    private $headerLine = false;

    /**
     * @var bool|int
     */
    private $lastLine = false;

    /**
     * @param $path
     * @param string $mode
     * @param bool   $headersInFirstRow
     */
    public function __construct($path, $mode = 'r+', $headersInFirstRow = true)
    {
        parent::__construct($path, $mode);
        $this->_line    = 0;
        $this->headersInFirstRow = $headersInFirstRow;
    }

    /**
     * @return bool
     */
    public function getHeaders()
    {
        $this->init();

        return $this->headers;
    }

    /**
     * @return array|bool
     */
    public function getRow()
    {
//<<<<<<< HEAD
//        $this->_has_error = false;
//        if (($row = fgetcsv($this->_handle, 4096, $this->_delimiter, $this->_enclosure)) !== false) {
//
//            $row = array_map(function($key) {
//                return trim($key);
//            }, $row);
//
//            if ($this->getForceUtf8()) {
//              $row = array_map(function($key) {
//                return mb_check_encoding($key, 'UTF-8') ? $key : utf8_encode($key);
//              }, $row);
//            }
//
//            if ($this->getFixEscaped()) {
//              $row = array_map(function($key) {
//                return str_replace(array('\t','\n'), array("\t", "\n"), $key);
//              }, $row);
//            }
//
//            $this->_line++;
//            if ($this->asArray()) {
//              return $row;
//            } elseif (empty($this->_headers) ) {
//              if ($this->_codified_fields <> AbstractBase::FIELDNAME_PRESERVE)
//              {
//                    foreach ($row as $idx=>$column_name)
//                    {
//		                    $y = preg_replace('/(?<=[a-z])(?=[A-Z])/', '_', $column_name);
//                        $row[$idx] =  str_replace('-', '_', \Survos\Lib\tt::name_to_code($y));
//                        if ($this->_codified_fields == AbstractBase::FIELDNAME_NO_SYMBOLS)
//                        {
//                            $row[$idx] =  str_replace('_', '', $row[$idx]);
//                        }
//                    }
//              }
//              $this->_headers = $row;
//              $this->createFields($row);
//              $this->_header_count = count($row);
//              return $this->getRow();
//            } else {
//              if (count($row) <> $this->_header_count) {
//                if ($this->_unnamed_extra_data_var) {
//                    try {
//                        $normal = array_slice($row, 0, $this->_header_count);
//                        $ret[$this->_unnamed_extra_data_var] = array_slice($row, $this->_header_count);
//                        return $ret;
//                        // throw new \Exception("Bad Data, wrong number of rows");
//                    }
//                    catch (\Exception $e)
//                    {
//                        $this->_has_error = true;
//                        $this->_error = "Bad Data, wrong number of columns";
//                        return $row;
//                    }
//
//                } else {
//                    $this->_has_error = true;
//                    $this->_error = "Bad Data, wrong number of columns, define unnamedExtraDataVar";
//                    // throw new \Exception("Bad Data, wrong number of rows");
//                    return $row;
//                }
//              }
//              try {
//                $ret = array_combine($this->_headers, $row);
//                // go through each field and and see what we can learn.
//                foreach ($this->fields as $fieldname=>$field) {
//                     $field->check($row[$field->idx]);
//                }
//                // now go through all the fields that are named "" and add them to extra
//                  $unnamed = [];
//                  if ($this->_unnamed_extra_data_var) {
//                      foreach ($this->_headers as $idx=>$columnName) {
//                          if (empty($columnName)) {
//                              if ($row[$idx] != '') {
//                                  $unnamed[] = $row[$idx];
//                              }
//                          }
//                      }
//                      $ret[$this->_unnamed_extra_data_var] = $unnamed;
//                  }
//
//              } catch (\ErrorException $e) {
//                $ret = $row;
//              }
//
//              return $ret;
//            }
//=======
        $this->init();
        if ($this->isEof()) {
            return false;
        }

        $row = $this->getCurrentRow();
        $isEmpty = $this->rowIsEmpty($row);

        if ($this->isEof() === false) {
            $this->handle->next();
        }

        if ($isEmpty === false) {
            return ($this->headers && is_array($this->headers)) ? array_combine($this->headers, $row) : $row;
        } elseif ($isEmpty) {
            // empty row, transparently try the next row
            return $this->getRow();
//>>>>>>> github/master
        } else {
            return false;
        }
    }



    public function getFields()
    {
        return $this->fields;
    }

    public function hasError() {
      return $this->_has_error;
    }

    public function getError() {
      return $this->_error;
    }

    public function advanceToRow($rowNumber)
    {
        while ($this->getLineNumber() < $rowNumber) {
            $this->getRow();
        }
    }

    /**
     * @return bool
     */
    public function isEof()
    {
        return $this->handle->eof();
    }

    /**
     * @return array
     */

    public function getAll()
    {
        $data = array();
        while ($row = $this->getRow()) {
            $data[] = $row;
        }

        return $data;
    }

    /**
     * @return int zero-based index
     */
    public function getLineNumber()
    {
        return $this->handle->key();
    }

    /**
     * @return int zero-based index
     */
    public function getLastLineNumber()
    {
        if ($this->lastLine !== false) {
            return $this->lastLine;
        }

        $this->handle->seek($this->handle->getSize());
        $lastLine = $this->handle->key();

        $this->handle->rewind();

        return $this->lastLine = $lastLine;
    }

    /**
     * @return array
     */
    public function getCurrentRow()
    {
        return str_getcsv($this->handle->current(), $this->delimiter, $this->enclosure);
    }

    /**
     * @param $lineNumber zero-based index
     */
    public function advanceTo($lineNumber)
    {
        if ($this->headerLine > $lineNumber) {
            throw new \LogicException("Line Number $lineNumber is before the header line that was set");
        } elseif ($this->headerLine === $lineNumber) {
            throw new \LogicException("Line Number $lineNumber is equal to the header line that was set");
        }

        if ($lineNumber > 0) {
            $this->handle->seek($lineNumber - 1);
        } // check the line before

        if ($this->isEof()) {
            throw new \LogicException("Line Number $lineNumber is past the end of the file");
        }

        $this->handle->seek($lineNumber);
    }

    /**
     * @param $lineNumber zero-based index
     */
    public function setHeaderLine($lineNumber)
    {
        if ($lineNumber !== 0) {
            $this->headersInFirstRow = false;
        } else {
            return false;
        }

        $this->headerLine = $lineNumber;

        $this->handle->seek($lineNumber);

        // get headers
        $this->headers = $this->getRow();
    }

    protected function init()
    {
        if (true === $this->init) {
            return;
        }
        $this->init = true;

        if ($this->headersInFirstRow === true) {
            $this->handle->rewind();

            $this->headerLine = 0;

            $this->headers = $this->getRow();
        }
    }

    /**
     * @param $row
     * @return bool
     */
    protected function rowIsEmpty($row)
    {
        $emptyRow = ($row === array(null));
        $emptyRowWithDelimiters = (array_filter($row) === array());
        $isEmpty = false;

        if ($emptyRow) {
            $isEmpty = true;

            return $isEmpty;
        } elseif ($emptyRowWithDelimiters) {
            $isEmpty = true;

            return $isEmpty;
        }

        return $isEmpty;
    }

    public function setAsArray($as_array) {
      $this->_as_array = $as_array;
    }

    public function asArray() {
      return $this->_as_array;
    }

}
