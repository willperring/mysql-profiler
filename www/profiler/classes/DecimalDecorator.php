<?php

class DecimalDecorator extends ValueDecorator {

	protected $_decimals = 2;

	public function __construct( ValueDecoratable $decoratable, $decimals=2 ) {
		parent::__construct( $decoratable );
		$this->setDecimals( 2 );
	}

	public function setDecimals( int $decimals ) {
		if( is_null($decimals) )
			return;
		$this->_decimals = $decimals;
		return $this;
	}

	public function getValue( $value ) {
		return number_format( $this->_decoratable->getValue($value), $this->_decimals );
	}

}