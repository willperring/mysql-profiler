<?php

class StringDecorator extends ValueDecorator {

	protected $_template = null;

	public function __construct( ValueDecoratable $decoratable, $template=null ) {
		parent::__construct( $decoratable );
		$this->setTemplate( $template );
	}

	public function setTemplate( $template ) {
		$this->_template = $template;
		return $this;
	}

	public function getValue( $value ) {
		if( !is_string($this->_template) )
			Throw new Exception('StringDecorator requires a valid string template');
		return str_replace('{{value}}', $this->_decoratable->getValue($value), $this->_template );
	}
}