<?php

class Interpolator implements ValueDecoratable {

	private $_type;
	private $_max;
	private $_min;
	private $_debug = false;

	static private $_types = array(
		'percentage' => '_returnPercentage',
		'fraction'   => '_returnFraction',
	);

	static function factory( $type, $min=null, $max=null ) {
		if( ! array_key_exists(strtolower($type), static::$_types) )
			Throw new Exception("Interpolator::factory() doesn't understand type '{$type}'");
		return new Interpolator($type, $min, $max);
	}

	private function __construct( $type, $min=null, $max=null ) {
		$this->_type = $type;
		if( !is_null($min) ) $this->setMin($min);
		if( !is_null($max) ) $this->setMax($max);
	}

	public function setMax( $max ) {
		$this->_max = $max;
		return $this;
	}

	public function setMin( $min ) {
		$this->_min = $min;
		return $this;
	}

	public function setDebug( $state ) {
		$this->_debug = $state;
		return $this;
	}

	public function getValue( $value ) {
		return call_user_func( [$this, static::$_types[$this->_type]], $value );
	}

	private function _returnFraction( $value ) {

		if( ! is_numeric($value) )
			Throw new Exception("Interpolation value '{$value}' cannot be interpolated");
		if( ! is_numeric($this->_min) )
			Throw new Exception("Minimum value '{$this->_min}' cannot be interpolated");
		if( ! is_numeric($this->_max) )
			Throw new Exception("Maximum value '{$this->_min}' cannot be interpolated");

		$result = ( $value - $this->_min ) / ( $this->_max - $this->_min );
		return $result;
	}

	private function _returnPercentage( $value ) {
		return $this->_returnFraction($value) * 100 ;
	}

}