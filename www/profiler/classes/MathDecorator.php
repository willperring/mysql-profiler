<?php

class MathDecorator extends ValueDecorator {

	const OPERATOR_ADD      = 1;
	const OPERATOR_SUBTRACT = 2;
	const OPERATOR_MULTIPLY = 3;
	const OPERATOR_DIVIDE   = 4;
	const OPERATOR_EXPONENT = 5;

	private $_operand;
	private $_operator;

	public function __construct( ValueDecoratable $decoratable, $operand=null, $operator=null ) {
		parent::__construct( $decoratable );
		$this->setOperation( $operand, $operator );
	}

	public function setOperation( $operand, $operator ) {
		$this->_operand  = $operand;
		$this->_operator = $operator;
	}

	public function getValue( $value ) {

		$startingVal = $this->_decoratable->getValue( $value );

		switch( $this->_operator ) {
			case MathDecorator::OPERATOR_ADD:
				return $startingVal + $this->_operand;
			case MathDecorator::OPERATOR_SUBTRACT:
				return $startingVal - $this->_operand;
			case MathDecorator::OPERATOR_MULTIPLY:
				return $startingVal * $this->_operand;
			case MathDecorator::OPERATOR_DIVIDE:
				return $startingVal / $this->_operand;
			case MathDecorator::OPERATOR_EXPONENT:
				return pow($startingVal, $this->_operand);
			default:
				Throw new Exception("Invalid operator on MathDecorator");
		}

	}

}