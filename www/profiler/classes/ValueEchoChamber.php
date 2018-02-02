<?php

/**
 * An Echo Chamber than can have ValueDecorators applied
 *
 * The whole point of this class is to allow us to build decorator chains within views or actions,
 * but without obtaining their data from a base class - rather, this allows us to pass a value to
 * a stack of decorators and get the result.
 */
class ValueEchoChamber implements ValueDecoratable {

	public function getValue( $value ) {
		return $value;
	}

}