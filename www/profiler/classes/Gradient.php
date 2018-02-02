<?php

class Gradient {

	private $_advanced = false;
	private $_parts    = array();

	public function _construct( $advanced=false, $parts=array() ) {
		$this->_advanced = $advanced;
	}

	public function addStop( HexColor $color, $stopPosition=null ) {

		if ( !$this->_advanced ) {
			$this->_parts[] = $color;
		} else {
			if( is_null($stopPosition) ) {
				trigger_error( 'No stop provided for Advanced gradient, aborted', E_USER_WARNING );
				return;
			}
			$this->_parts[ $stopPosition ] = $color;
		}

		return $this;
	}

	public function getColor( $value ): HexColor {
		return ( $this->_advanced )
			? $this->_getColorAdvanced( $value )
			: $this->_getColorSimple( $value );
	}

	private function _getColorSimple( $requestedValue ): HexColor {

		$value = Max( 0, Min($requestedValue, 100) );
		if( $value != $requestedValue ) {
			trigger_error("Gradient::_getColorSimple() - requested value {$requestedValue}"
				. " is out of bounds, using {$value} instead", E_USER_WARNING);
		}

		$numParts = count($this->_parts);
		if( $numParts < 2 )
			Throw new Exception("Gradient Requires at least two parts for a simple interpolation");

		// Take one off as we have to 'stretch' to the end.
		// I.E, three colours, two 'bands' to interpolate.
		$stopPercentage = 100 / ( $numParts - 1 );

		$lowerEdge = 0;
		$threshold = 1; // purely to stop divide by zero
		for( $i=0; $i<$numParts; $i++ ) {
			$threshold = ($i + 1) * $stopPercentage;
			if( $value < $threshold ) {
				break;
			}
			$lowerEdge = $threshold;
		}

		$color1 = $this->_parts[ $i ];
		$color2 = $this->_parts[ $i + 1 ];

		if( empty($color2) )
			return $color1;

		$delta  = ( $value - $lowerEdge) / ( $threshold - $lowerEdge );

		return $color1->interpolate( $color2, $delta );
	}

	private function _getColorAdvanced( $value ) {

	}

}