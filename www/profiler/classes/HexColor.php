<?php

class HexColor {

	static $_alphaAllowed = true;

	private $_red;
	private $_green;
	private $_blue;
	private $_alpha;

	public static function setAlphaAllowed( bool $state ) {
		static::$_alphaAllowed = $state;
	}

	public static function createFromHSL( $hue, $sat, $lum ) {

		// Convert to fraction values
		$conv = Interpolator::factory('fraction', 0, 255 );
		$conv = new LimitDecorator($conv);
		$conv->setLimits(0, 1);

		$hue = $conv->getValue( $hue );
		$sat = $conv->getValue( $sat );
		$lum = $conv->getValue( $lum );

		// Start with a grey
		$rgb = array($lum, $lum, $lum);
		$v   = ($lum <= 0.5) ? ($lum * (1.0+$sat)) : ($lum + $sat - $lum * $sat);

		if( $v > 0 ) {
			$m    = $lum + $lum - $v;
			$sv   = ($v - $m ) / $v;
			$hue *= 6.0;

			$sextant = floor($hue);
			$fract   = $hue - $sextant;
			$vsf     = $v * $sv * $fract;
			$mid1 = $m + $vsf;
			$mid2 = $v - $vsf;

			$rgb = array(
				0 => [ $v,    $mid1, $m    ],
				1 => [ $mid2, $v,    $m    ],
				2 => [ $m,    $v,    $mid1 ],
				3 => [ $m,    $mid2, $v    ],
				4 => [ $mid1, $m,    $v    ],
				5 => [ $v,    $m,    $mid2 ]
			)[ (int) $sextant ];
		}

		return new HexColor( $rgb[0] * 255, $rgb[1] * 255, $rgb[2] * 255 );
	}

	public function __construct( $red, $green, $blue, $alpha=255 ) {
		$this->setRed( $red )
			->setBlue( $blue )
			->setGreen( $green )
			->setAlpha( $alpha );
	}

	public function setRed( $red ) {
		$this->_assetValidColorValue( $red, 'Red' );
		$this->_red = $red;
		return $this;
	}

	public function setGreen( $green ) {
		$this->_assetValidColorValue( $green, 'Green' );
		$this->_green = $green;
		return $this;
	}

	public function setBlue( $blue ) {
		$this->_assetValidColorValue( $blue, 'Blue' );
		$this->_blue = $blue;
		return $this;
	}

	public function setAlpha( $alpha ) {
		$this->_assetValidColorValue( $alpha, 'Alpha' );
		$this->_alpha = $alpha;
		return $this;
	}

	public function asHex(): string {
		$parts = $this->_getValidParts();
		$hex   = '#';

		foreach( $parts as $part ) {
			$hex .= str_pad(dechex($part),2,0, STR_PAD_LEFT);
		}

		return $hex;
	}

	public function asRgba( $overrideAlphaAllowed=false ): string {
		if( !static::$_alphaAllowed && !$overrideAlphaAllowed )
			return $this->asRgb();
		return "rgba({$this->_red}, {$this->_green}, {$this->_blue}, {$this->_alpha})";
	}

	public function asRgb( $ignoreAlpha=false ): string {
		if( $this->_alpha != 255 && static::$_alphaAllowed && !$ignoreAlpha )
			return $this->asRgba();
		return "rgb({$this->_red}, {$this->_green}, {$this->_blue})";
	}

	public function interpolate( HexColor $color2, $amount ) {
		return new HexColor(
			$this->_interpolateValues( $this->_red,   $color2->_red,   $amount),
			$this->_interpolateValues( $this->_green, $color2->_green, $amount),
			$this->_interpolateValues( $this->_blue,  $color2->_blue,  $amount),
			$this->_interpolateValues( $this->_alpha, $color2->_alpha, $amount)
		);
	}

	private function _interpolateValues( $val1, $val2, $delta ) {
		return round( $val1 + ( $delta * ($val2-$val1) ) );
	}

	private function _getValidParts() {

		$parts = array(
			$this->_red,
			$this->_green,
			$this->_blue
		);

		if( $this->_alpha != 255 && static::$_alphaAllowed ) {
			$parts[] = $this->_alpha;
		}

		return $parts;
	}

	private function _assetValidColorValue( $value, $color ) {
		if( !is_numeric($value) || $value > 255 | $value < 0 ) {
			Throw new Exception("HexColor cannot have a {$color} value of {$value}");
		}
	}

}