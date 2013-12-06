<?php

namespace EasyCSV;

class Field {
	var $is_numeric = true;
	var $is_unique = true;
	var $calc_value = ''; // if run through template
	var $max_value;
	var $min_value;
	var $max_length;
	var $min_length;
	var $type;
    var $idx = 0;
    var $column_name; // original name from CSV
    var $uniqueValues = [];
    var $choiceCandidate = true;

	function __construct($name, $column_name, $idx, $type='') {
	  $this->column_name = $column_name;
	  $this->idx = $idx;
	  $this->field_name = $name;
	  if ($type) {
		  $this->type = $type;
      }
	}

	function default_index() {
	  return ''; // turn off for now, not worth the trouble
	  if ($this->is_unique)
	    return 'unique';
	  return '';
	}

	function check($value) {
        # printf("<li>Checking %s (%s)", $this->field_name, $value);
        if ($value=='') {
            $this->is_unique = false;
            return;
        }

	  // if it's the first row, then set defaults
	  if (!isset($this->max_length)) {
	    $this->max_length = $this->min_length = strlen($value);
	    $this->max_value = $this->min_value = $value;
	  }
		if ( !is_numeric($value) ) {
			$this->is_numeric = false;
	  }
  	if ($value > $this->max_value) {
				$this->max_value = $value;
		}
		if ($value < $this->min_value) {
				$this->min_value = $value;
		}
  	if (strlen($value) > $this->max_length) {
				$this->max_length = strlen($value);
		}
  	if (strlen($value) < $this->min_length) {
				$this->min_length = strlen($value);
		}

        // ugh, introduces a dependency we don't really need!
        if (count($this->uniqueValues) < 32) {
            $this->uniqueValues[\Survos\Lib\tt::name_to_code($value)] = $value;
        } else {
            $this->choiceCandidate = false;
        }

		if ($this->is_unique) {

            if (!empty($this->seen[$value])) {
                // if value seen before, not unique index
          	    $this->is_unique = false;
            }
            else {
                $this->seen[$value] = 1;
            }

            if ($this->is_numeric) {
                if (strpos($value, '.') !== false) {
                    // if float, not unique index
                    $this->is_unique = false;
                }
            }
            elseif ($this->max_length > 64) {
                // if long string, not unique index
                $this->is_unique = false;
            }

            if (!$this->is_unique) {
                // empty the table
	    		unset($this->seen);
    		}

		}
	}

	function dump() {
		printf("<li>%s:  %s Max %s %s", $this->field_name, $this->is_numeric ? "Integer" : "String", $this->max_value,
			$this->is_unique ? "UNIQUE" : '');
	}

  function guess_type() { // returns string, number, float, boolean or text

	if ($this->is_numeric) {

			if ($this->max_value == 1) { // probably a boolean
				$type = 'boolean';
			}
            else {
                $type = strpos($this->min_value, '.') === false &&
                    strpos($this->max_value, '.') === false ? 'number' : 'float';
            }
		} else {
			$type = $this->max_length > 255 ? 'text' : "string";
		}
		return $type;
  }

  function get_type()
  {

	  if (!empty($this->type)) {
	    $type = $this->type;
	  } elseif ($this->is_numeric) {

			if ($this->max_value == 1) { // probably a boolean
				$type = sprintf('tinyint(1) unsigned ');
			} else {
			  if (is_float($this->max_value)) {
				  $type = sprintf('float(11) /* max: %s */', $this->max_value); // could also be SMALLINT, etc.
				} else {
				  $type = sprintf('int(11) /* max: %d */', $this->max_value); // could also be SMALLINT, etc.
				}
			}
		} else {
			$type = $this->max_length > 255 ? 'text' : "varchar($this->max_value)";
		}
		return $type;
  }

	function mysql_frag() {
		# return(sprintf("`%s` %s NOT NULL", $this->field_name, $type));
		return(sprintf("`%s` %s NOT NULL", $this->field_name, $this->get_type()));

	}

	function set_calculated_value($calc_value) {
	    $this->calc_value = $calc_value;
	}

}
