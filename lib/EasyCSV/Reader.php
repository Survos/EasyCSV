<?php

namespace EasyCSV;

use EasyCSV\Field;

class Reader extends AbstractBase
{
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
              if ($this->_codified_fields)
              {
                    foreach ($row as $idx=>$column_name)
                    {
                        $row[$idx] =  \Survos\Lib\tt::display_to_code(
                    // insert underscores before camel caps
                    preg_replace('/(?<=[a-z])(?=[A-Z])/', '_', $column_name)
            );

                    }
              }
              $this->_headers = $row;
              $this->createFields($row);
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
                // go through each field and and see what we can learn.
                foreach ($this->fields as $fieldname=>$field) {
                     $field->check($row[$field->idx]);
                }
              } catch (\ErrorException $e) {
                $ret = $row;
              }

              return $ret;
            }
        } else {
            return false;
        }
    }

    // create $this->field based on header names
    function createFields($data) {
    $fields = array();
    $possible_points = array();
    // we need to preserve column name, for compatibility with EasyCSV.
    for ($c=0; $c < count($data); $c++) {
        $column_name = $data[$c];
        $field_name = \Survos\Lib\tt::display_to_code(
            // insert underscores before camel caps
            preg_replace('/(?<=[a-z])(?=[A-Z])/', '_', $column_name)
        );
        $i = 1;
        $original_field_name = $field_name;
        while (isset($fields[$field_name])) {
            $i++;
            $field_name = $original_field_name . '_' . $i;
        }
    	$fields[$field_name] =  new field($field_name, $column_name, $c);
    	/* old code allowed presetting fieldtypes
    	  empty($this->datatypes[$field_name]) ? null :
    	    $this->datatypes[$field_name]['type']);
    	*/
        $this->csv_field_count = count($fields); //
        // check for possible point fields
        if (preg_match('{(.*?)_?(latitude|longitude)}', $field_name, $m)) {
            $point_fieldname = $m[1] ?: 'location';

            $possible_points[$point_fieldname][$m[2]] = $field_name;
        }

    }
    return $this->fields = $fields;
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