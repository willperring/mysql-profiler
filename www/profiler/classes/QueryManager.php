<?php

if( !defined('PROFILER_APP') )
	die('No direct access');

class QueryManager {

	private $_parts;

	public function __construct( array $parts=null ) {
		$this->_parts = ( $parts) ? $parts : $_GET ;
	}

	public function clone() {
		return new QueryManager( $this->_parts );
	}

	public function set( $key, $value ) {
		$this->_parts[ $key ] = $value;
		return $this;
	}

	public function unset( $key ) {
		if( is_array($key) ) {
			foreach( $key as $keyItem ) {
				$this->unset($keyItem);
			}
			return $this;
		}
		unset( $this->_parts[$key] );
		return $this;
	}

	public function export( &$string ) {
		$string = $this->getQueryString();
		return $this;
	}

	public function toggle( $key, array $options=array(0,1), $direction=1 ) {

		if( !key_exists($key, $this->_parts) ) {
			return $this->set( $key, $options[0] );
		}

		$currentIndex = array_search( $this->getParam($key), $options );
		if( $currentIndex === false ) {
			return $this->set( $key, $options[0] );
		}

		$currentIndex += $direction;
		$optCount = count($options);
		$lpBuster = 0; // LoopBuster

		while( ($currentIndex < 0) && ($lpBuster < 10) ) {
			$currentIndex += $optCount;
			$lpBuster++;
		}

		while( ($currentIndex >= $optCount) && ($lpBuster < 20) ) {
			$currentIndex -= $optCount;
			$lpBuster++;
		}

		return $this->set( $key, $options[$currentIndex] );
	}

	public function getQueryString() {
		return ( $this->_parts && count($this->_parts) )
			? '?' . http_build_query($this->_parts) : '' ;
	}

	public function getParam( $key ) {
		return @$this->_parts[ $key ];
	}

	public function getParamOrDefault( $key, $default, $set=false ) {
		if( !key_exists($key, $this->_parts) ) {
			if( $set ) $this->set( $key, $default );
			return $default;
		}
		return $this->_parts[ $key ];
	}

	public function paramIsTruthy( $key ) {
		return !! @$this->_parts[ $key ];
	}

	public function paramIsFalsey( $key ) {
		return ! @$this->_parts[ $key ];
	}

}