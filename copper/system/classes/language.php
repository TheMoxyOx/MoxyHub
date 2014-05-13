<?php

class Language {

	// object variables
	var $DB;

	function main() {
			// create a database object
		$this->DB = new DBConnection(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
		
		// if we can't process the language file fall back to the default language
		if (!$this->process(CU_LANGUAGE_PATH . Settings::get('DefaultLanguage') . CU_LANGUAGE_EXT)) {
			// if we can't process the default language file, we need to borg
			if (!$this->process(CU_LANGUAGE_PATH . CU_DEFAULT_LANGUAGE . CU_LANGUAGE_EXT)) {
				exit('FATAL ERROR - Default Language File Not Found');
			}
		}
	}

	function process($file) {
		// assume we fail
		$return = false;
		if (file_exists($file)) {
			$lines = file($file);
			$count = 0;
			  
			// load the overwritten tokens first, if any 
			$SQL = CU_SQL_LANGUAGE_OVERRIDE;
		   $RS = new DBRecordset();
			$RS->Open($SQL, $this->DB);
			while (!$RS->EOF()) {
				define($RS->Field('Token'), $RS->Field('Value'));
				$originals[$RS->Field('Token')] = 'replace';
				$RS->MoveNext();
			}
	 
			// load the pretend ini file into memory
			// note: we don't use parse_ini_file because it doesn't like =, ( and )
			// as well as an assortment of other delicious charaters
			foreach ($lines as $line) {
				$line = explode(' = ', $line, 2);
				$token = ltrim(rtrim($line[0]));
				$value = ltrim(rtrim($line[1]));
				$tokens[$token] = $value;
				// if the tokens not already defined, define it
				if (!defined($token)) {
					define($token, $value);
				}
				// if it is in the originals array it means that we overwrote it with
				// a value from the database, so we need to define the original value
				/*if ($originals[$token] == 'replace') {
					define('ORIGINAL_' . $token, $value);
				}*/
			}
			
			$return = true;
		}
		return $return;
	}
}
