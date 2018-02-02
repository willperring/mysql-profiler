<?php

abstract class ValueDecorator implements ValueDecoratable {

	protected $_decoratable;

	public function __construct( ValueDecoratable $decoratable ) {
		$this->_decoratable = $decoratable;
	}

	public function clone( ValueDecoratable $decoratable ) {
		$newDecorator = clone( $this );
		$newDecorator->_decoratable = $decoratable;
		return $newDecorator;
	}

	abstract public function getValue( $value );
}