<?php

if( !defined('PROFILER_APP') )
	die('No direct access');

class QueryLog {

	use Metadata;

	private $_data;
	private $_profileId;

	public function __construct( $data, $profileId=null ) {
		$this->_data      = $data;
		$this->_profileId = $profileId;
	}

	public function getType() {
		return @$this->_data['type'] ?: "Unknown Type";
	}

	public function getStart() {
		return @$this->_data['start'] ?: 0;
	}

	public function getTime() {
		return @$this->_data['time'] ?: 0;
	}

	public function getTrace() {
		return @$this->_data['trace'] ?: array();
	}

	public function getResult( $tokenize=0 ) {
		$res = @$this->_data['result'];
		return ( is_int($tokenize) && ($tokenize > 0) )
			? $this->tokenizeString( $res, $tokenize ) : $res ;
	}

	public function getSql( $tokenize=0 ) {
		$sql = @$this->_data['sql'] ?: '';
		return ( is_int($tokenize) && ($tokenize > 0) )
			? $this->tokenizeString( $sql, $tokenize ) : $sql ;
	}

	public function getTooltipHtml() {
		return "<b>{$this->getTableString()}</b></br>"
			. "<b>".number_format($this->getTime()*1000,2)."ms</b></br>"
			. $this->tokenizeString( $this->getSql(), 40, "<br/>");
	}

	public function getStartTimeString() {
		$timestamp = $this->_data['start'] ?: 0;
		$parts     = explode('.', $timestamp);
		return date('H:i:s.', $parts[0])
			. str_pad($parts[1], 4, 0, STR_PAD_RIGHT);
	}

	public function getExecutionTimeMs() {
		return number_format( @$this->_data['time'] * 1000, 2 );
	}

	public function getTableString() {
		return implode('.', array_filter(@$this->_data['table']));
	}

	public function getFullTable() {
		return implode('<br/>', array_filter(@$this->_data['table']));
	}

	public function tokenizeString( $string, $maxchars=80, $replace="\n", $breakchars="!{}\"'\\/.,;:" ) {

		$result = '';
		$lpbust = 0;

		$maxIterations = strlen($string);

		while( (strlen($string) > $maxchars) && ($lpbust < $maxIterations) ) {

			$lpbust++;

			// We can 'cut to' a space if there's one - look from the end backwards
			$wsChar = strrpos(substr($string, 0, $maxchars), ' ');
			if( $wsChar !== false ) {
				$result .= substr( $string, 0, $wsChar+1 );
				$string  = substr( $string, $wsChar+1 );
				continue;
			}

			// Manually scan for any of the characters we've determined valid breakers.
			$scanLength = min($maxchars,strlen($string));
			for( $i=$scanLength; $i>0; $i-- ) {
				if( strpos($breakchars, $string[$i]) !== false ) {
					$result .= substr( $string, 0, $i+1 ) . $replace;
					$string  = substr( $string, $i+1 );
					continue 2;
				}
			}

			$result .= substr( $string, 0, $maxchars+1 ) . $replace;
			$string  = substr( $string, $maxchars+1 );
		}

		return $result . $string;
	}

	public static function getComparisonFunction( $field, $dir ) {

		switch( strtolower($field) ) {
			case "table":
				$cb = function($a,$b) { return strcmp($a->getTableString(), $b->getTableString()); };
				break;
			case "sql":
				$cb = function($a,$b) { return strcmp($a->getSql(), $b->getSql()); };
				break;
			case "type":
				$cb = function($a,$b) { return strcmp($a->getType(), $b->getType()); };
				break;
			case "start":
				$cb = function($a,$b) {
					$diff = $a->getStart() - $b->getStart();
					return ( $diff > 0 ) ? 1 : ( $diff < 0 ? -1 : 0 );
				};
				break;
			case "time":
				$cb = function($a,$b) {
					$diff = $a->getTime() - $b->getTime();
					return ( $diff > 0 ) ? 1 : ( $diff < 0 ? -1 : 0 );
				};
				break;
			default:
				$cb = function($a,$b) { return 0; };
				break;
		}

		// If we're descending, wrap the callback function in a lambda that inverts the parameters.
		return ( is_string($dir) && strtolower($dir) == 'desc' )
			? function($a,$b) use ($cb) { return $cb($b,$a); }
			: $cb;
	}

}