<?php
/**
 * The echo format class. oh yeah.
 * $Id$
 */

class EF
{
	public static function raw($str) { echo Format::raw($str); }
	public static function t($str) { echo Format::text($str); }
	public static function money($str) { echo Format::money($str); }
	public static function val($str) { echo Format::val($str); }
	public static function v($str) { echo Format::val($str); }
	public static function options($set, $current = null, $value_var = 'id', $text_var = 'value') { echo Format::options($set, $current, $value_var, $text_var); }
}
