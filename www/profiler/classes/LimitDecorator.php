<?php

class LimitDecorator extends ValueDecorator {

	private $_min = null;
	private $_max = null;

	public function __construct( ValueDecoratable $decoratable, $min=null, $max=null ) {
		parent::__construct( $decoratable );
		$this->setLimits( $min, $max );
	}

	public function setLimits( $min, $max ) {
		$this->setMin( $min );
		$this->setMax( $max );
	}

	public function setMin( $min ) {
		if( !is_null($min) )
			$this->_min = $min;
	}

	public function setMax( $max ) {
		if( !is_null($max) )
			$this->_max = $max;
	}

	public function getValue( $value ) {

		$value = $this->_decoratable->getValue( $value );

		if( !is_null($this->_min) )
			$value = max( $value, $this->_min );
		if( !is_null($this->_max) )
			$value = min( $value, $this->_max );

		return $value;
	}

}