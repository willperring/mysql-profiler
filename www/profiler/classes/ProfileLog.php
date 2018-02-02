<?php

if( !defined('PROFILER_APP') )
	die('No direct access');

class ProfileLog {

	use Metadata;

	public  $sourceFile = null;
	private $content    = null;

	private $data    = null;
	private $queries = null;

	public function __construct( $filename ) {
		$this->sourceFile = $filename;
		$this->content    = $this->readContents( $filename );
	}

	public function getFilename() {
		return array_pop( explode(DIRECTORY_SEPARATOR, $this->sourceFile) );
	}

	public function getQueries() {
		// Regenerate on request in case meta has changed
		$this->parseQueries();
		return $this->queries;
	}

	public function getCount() {
		if( is_null($this->data) )
			$this->parseContents();
		return $this->data['count'];
	}

	public function getExecutionTime() {
		if( is_null($this->data) )
			$this->parseContents();
		return (float) $this->data['totalTime'];
	}

	public function getSaveTime() {
		if( is_null($this->data) )
			$this->parseContents();
		return $this->data['saveTime'];
	}

	public function getScript() {
		if( is_null($this->data) )
			$this->parseContents();
		return $this->data['script'];
	}

	public function getRequest() {
		if( is_null($this->data) )
			$this->parseContents();
		return $this->data['request'];
	}

	public function getChartData() {

		$queries  = $this->getQueries();
		$minStart = 99999999999999999;
		foreach( $queries as $query ) {
			$minStart = min( $query->getStart(), $minStart );
		}

		$chartData = array();
		$tables    = array();
		$i = 0;
		foreach( $queries as $query ) {
			$tables[ $query->getTableString() ] = true;
			$chartData[] = array(
				$query->getTableString(),
				'',
				$query->getTooltipHtml(),
				$query->getStart() - $minStart,
				$query->getStart() - $minStart + $query->getTime()
			);
		}

		return array(
			'data'   => $chartData,
			'tables' => count($tables)
		);
	}

	private function readContents( $filename ) {
		$fh = fopen( $filename, 'r' );

		$contents = '';
		while( !feof($fh) )
			$contents .= fgets($fh);
		fclose( $fh );

		return $contents;
	}

	private function parseContents() {
		$this->data = json_decode( $this->content, true );
	}

	private function parseQueries() {
		if( is_null($this->data) )
			$this->parseContents();

		$queries = array();
		foreach( $this->data['queries'] as $query ) {
			$query = new QueryLog( $query );
			$query->copyMetaFrom( $this );
			$queries[] =  $query;
		}

		$this->queries = $queries;
	}

	static function getComparisonFunction( $field, $dir ) {

		// We need an appropriate lambda function to return.
		switch( strtolower($field) ) {
			case "name":
				$cb = function($a,$b) { return strcmp($a->getFilename(), $b->getFilename()); };
				break;
			case "request":
				$cb = function($a,$b) { return strcmp($a->getRequest(), $b->getRequest()); };
				break;
			case "time":
				$cb = function($a,$b) {
					$diff = $a->getExecutionTime() - $b->getExecutionTime();
					return ( $diff > 0 ) ? 1 : ( $diff < 0 ? -1 : 0 );
				};
				break;
			case "save":
				$cb = function($a,$b) {
					$diff = $a->getSaveTime() - $b->getSaveTime();
					return ( $diff > 0 ) ? 1 : ( $diff < 0 ? -1 : 0 );
				};
				break;
			case "count":
				$cb = function($a,$b) { return $a->getCount() - $b->getCount(); };
				break;
			default:
				throw new Exception("Unable to determine comparison function for '{$field}'");
		}

		// If we're descending, wrap the callback function in a lambda that inverts the parameters.
		return ( is_string($dir) && strtolower($dir) == 'desc' )
			? function( $a, $b ) use ( $cb ) { return $cb($b,$a); }
			: $cb;
	}

}