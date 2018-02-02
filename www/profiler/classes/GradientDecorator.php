<?php

class GradientDecorator extends ValueDecorator {

	protected $_gradient;
	protected $_useKeys;

	public function __construct( ValueDecoratable $decoratable, Gradient $gradient=null ) {
		parent::__construct( $decoratable );
		$this->setGradient( $gradient );
	}

	public function setGradient( Gradient $gradient ) {
		$this->_gradient = $gradient;
	}

	public function getValue( $value ) {
		if( is_null($this->_gradient) )
			Throw new Exception("GradientDecorator: No assigned Gradient");
		return $this->_gradient->getColor( $this->_decoratable->getValue($value) )->asHex();
	}

	function __clone() {
		$this->_gradient = clone $this->_gradient;
	}

}