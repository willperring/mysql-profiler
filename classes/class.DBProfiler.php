<?php

class DBProfiler {

	private $_id      = null;
	private $_queries = array();

	private $_startTime = null;
	private $_outputDir = null;


	public function __construct() {

		$this->_id = date('YmdHis')
			. '_'
			. uniqid()
			. str_replace('/', '_', $_SERVER['PHP_SELF'])
			. '.dbprofilelog';

	}

	public function setOutputDir( $path ) {
		if( file_exists($path) && is_dir($path) && is_writable($path) ) {
			$this->_outputDir = $path;
			return;
		}
		throw new Exception("DBProfiler Output dir '{$path}' is not writeable");
	}

	public function startTimer() {
		if( $this->_startTime != null ) {
			trigger_error("DBProfile::startTimer() is already running");
		}
		$this->_startTime = microtime(true);
		return $this;
	}

	public function addQuery( $type, $sql, $result=null ) {
		if( $this->_startTime != null ) {
			$start = $this->_startTime;
			$end = microtime( true );
			$this->_startTime = null;
		} else {
			$start = -1; $end = -1;
		}
		$this->_queries[] = new DBProfilerQuery($type, $sql, $start, $end, debug_backtrace(), $result);
		$this->_startTime = null;
	}

	private function _getJsonPayload() {

		$executionTime = 0;
		$queryData     = array();

		foreach( $this->_queries as $query ) {
			$queryData[] = $query->getJsonPayload();
			$executionTime += $query->getTime();
		}

		return array(
			'count'     => count($queryData),
			'script'    => $_SERVER['PHP_SELF'],
			'request'   => $_SERVER['REQUEST_URI'],
			'totalTime' => number_format( $executionTime*1000, 2),
			'queries'   => $queryData,
			'saveTime'  => time(),
			'cookie'    => $_COOKIE,
			'reqData'   => $_REQUEST,
			'server'    => $_SERVER,
		);
	}

	function __destruct() {
		if( !! $this->_outputDir ) {
			try {
				$fh = fopen( $this->_outputDir . $this->_id, 'w' );
				fwrite( $fh, json_encode( $this->_getJsonPayload() ) );
				fclose( $fh );
			} catch( Exception $e ) {
				trigger_error("Couldn't write DBProfile Log: " . $e->getMessage() );
			}
		}
	}

}

class DBProfilerQuery {

	private $_type;
	private $_sql;
	private $_start;
	private $_end;
	private $_trace;
	private $_result;

	public function __construct( $type, $sql, $start, $end, $trace, $result ) {
		$this->_type   = $type;
		$this->_sql    = $sql;
		$this->_start  = $start;
		$this->_end    = $end;
		$this->_trace  = ( $trace ) ? $trace : array();
		$this->_result = serialize($result);
	}

	public function __toString() {
		$time = ( $this->_start == -1 )
			? "No data"
			: number_format($this->getTime()*1000, 2);
		return "<tr>
			<td>{$this->_type}</td>
			<td>{$time}</td>
			<td>{$this->_sql}</td> 
		</tr>";
	}

	public function getTime() {
		return $this->_end - $this->_start;
	}

	public function getTable() {
		$match = preg_match('/(?:FROM|INTO|UPDATE) (?:(?<schema>[A-Za-z0-9_]+)\.)?(?<table>[A-Za-z0-9_]+) /i', $this->_sql, $matches);
		if( $match ) {
			return array_intersect_key(
				$matches,
				array_flip(array('schema', 'table'))
			);
		}
		return array(
			'schema' => '! UNKNOWN !',
			'table'  => '! UNKNOWN !',
		);
	}

	public function getJsonPayload() {

//		if( is_array($this->_result) || is_object($this->_result) )
//			$result = print_r($this->_result, true);
//		else
//			$result = $this->_result;

		return array(
			'type'   => $this->_type,
			'table'  => $this->getTable(),
			'sql'    => $this->_sql,
			'start'  => $this->_start,
			'time'   => $this->getTime(),
			'trace'  => $this->_trace,
			'result' => $this->_result,
			'when'   => time()
		);
	}

}