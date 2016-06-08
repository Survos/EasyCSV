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
			$this->uniqueValues[self::name_to_code($value)] = $value;
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

###########################
	static function name_to_code( $name, $max_length = 0, $allowedChars='') {
		# New version of display_to_code(), using hyphens
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
		// old version
		if ($allowedChars === true) {
			$allowedChars = '.';
		}
		$name = preg_replace($from, $to, $name); # remove accents
		# Lowercase and change non-alphanumerics to hyphens:
		$name = preg_replace("/[^a-z0-9$allowedChars]+/", '-', strtolower($name));
		$name = preg_replace('/^-/', '', $name); # trim initial hyphen, if any
		$name = preg_replace('/\b(\w)-(?=\w\b)/', '$1', $name); # scrunch things like 'u-s-v-i'
		if ($max_length) $name = substr($name, 0, $max_length);
		$name = preg_replace('/-$/', '', $name); # trim final hyphen, if any
		return $name;
	}

}
