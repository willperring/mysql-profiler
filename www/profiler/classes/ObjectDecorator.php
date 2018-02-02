<?php

class ObjectDecorator implements ValueDecoratable {

	private $_object;

	public function __construct( $object ) {
		$this->_object = $object;
	}

	public function getValue( $callDetails ) {
		if( is_array($callDetails) && is_string($callDetails[0])) {
			$method = $callDetails[0];
			$params = array();
		} else if( is_string($callDetails) ) {
			$method = $callDetails;
			$params = array();
		} else {
			Throw new Exception('Unusable call details passed to ObjectDecorator: ' . print_r($callDetails, true));
		}

		return call_user_func_array(
			array( $this->_object, $method ), $params
		);
	}

}