<?php
/*
 * Barcode Encoder tool
 * Original (C) 2008, Eric Stern
 * http://www.firehed.net, http://www.eric-stern.com
 *
 * This code may be re-used or re-distributed in any application, commercial
 * or non-commercial, free of charge provided that this credit remains intact.
 *
 */


class Code39 {
	protected static $code39 = array(
	'0' => 'bwbwwwbbbwbbbwbw','1' => 'bbbwbwwwbwbwbbbw',
	'2' => 'bwbbbwwwbwbwbbbw','3' => 'bbbwbbbwwwbwbwbw',
	'4' => 'bwbwwwbbbwbwbbbw','5' => 'bbbwbwwwbbbwbwbw',
	'6' => 'bwbbbwwwbbbwbwbw','7' => 'bwbwwwbwbbbwbbbw',
	'8' => 'bbbwbwwwbwbbbwbw','9' => 'bwbbbwwwbwbbbwbw',
	'A' => 'bbbwbwbwwwbwbbbw','B' => 'bwbbbwbwwwbwbbbw',
	'C' => 'bbbwbbbwbwwwbwbw','D' => 'bwbwbbbwwwbwbbbw',
	'E' => 'bbbwbwbbbwwwbwbw','F' => 'bwbbbwbbbwwwbwbw',
	'G' => 'bwbwbwwwbbbwbbbw','H' => 'bbbwbwbwwwbbbwbw',
	'I' => 'bwbbbwbwwwbbbwbw','J' => 'bwbwbbbwwwbbbwbw',
	'K' => 'bbbwbwbwbwwwbbbw','L' => 'bwbbbwbwbwwwbbbw',
	'M' => 'bbbwbbbwbwbwwwbw','N' => 'bwbwbbbwbwwwbbbw',
	'O' => 'bbbwbwbbbwbwwwbw','P' => 'bwbbbwbbbwbwwwbw',
	'Q' => 'bwbwbwbbbwwwbbbw','R' => 'bbbwbwbwbbbwwwbw',
	'S' => 'bwbbbwbwbbbwwwbw','T' => 'bwbwbbbwbbbwwwbw',
	'U' => 'bbbwwwbwbwbwbbbw','V' => 'bwwwbbbwbwbwbbbw',
	'W' => 'bbbwwwbbbwbwbwbw','X' => 'bwwwbwbbbwbwbbbw',
	'Y' => 'bbbwwwbwbbbwbwbw','Z' => 'bwwwbbbwbbbwbwbw',
	'-' => 'bwwwbwbwbbbwbbbw','.' => 'bbbwwwbwbwbbbwbw',
	' ' => 'bwwwbbbwbwbbbwbw','*' => 'bwwwbwbbbwbbbwbw',
	'$' => 'bwwwbwwwbwwwbwbw','/' => 'bwwwbwwwbwbwwwbw',
	'+' => 'bwwwbwbwwwbwwwbw','%' => 'bwbwwwbwwwbwwwbw');

	private $text;


	/**
	 * Code39 constructor.
	 * @param String $text The text of the content of the barcode.  Any alpha characters will be capitalized.  Astericks
	 * 			will be appended to the front and end of the string.
	 * @throws BarcodeException "Invalid text input."  The only valid inputs are [A-Z0-9-. $+\/%]
	 */
	public function __construct($text)
	{
		$text = strtoupper($text); // *UPPERCASE TEXT*

		if (!preg_match('/^[A-Z0-9-. $+\/%]+$/i', $text)) {
			throw new BarcodeException('Invalid text input.');
		}

		$this->text = '*' . $text . '*';
	}


	/**
	 * @return string The text of the content of the barcode.
	 */
	public function __toString()
	{
		return $this->text;
	}


	/**
	 * @param int $height
	 * @param int $widthScale
	 */
	protected function renderImage($height = 50, $widthScale = 1) {
		$barcode = imagecreate(strlen($this->text) * 16 * $widthScale, $height);

		$bg = imagecolorallocate($barcode, 255, 255, 0); //sets background to yellow
		imagecolortransparent($barcode, $bg); //makes that yellow transparent
		$black = imagecolorallocate($barcode, 0, 0, 0); //defines a color for black

		$chars = str_split($this->text);

		$colors = '';

		foreach ($chars as $char) {
			$colors .= self::$code39[$char];
		}

		foreach (str_split($colors) as $i => $color) {
			if ($color == 'b') {
				// imageLine($barcode, $i, 0, $i, $height-13, $black);
				imagefilledrectangle($barcode, $widthScale * $i, 0, $widthScale * ($i+1) -1 , $height-13, $black);
			}
		}

		//16px per bar-set, halved, minus 6px per char, halved (5*length)
		// $textcenter = $length * 5 * $widthScale;
		$textcenter = (strlen($this->text) * 8 * $widthScale) - (strlen($this->text) * 3);
		
		imagestring($barcode, 2, $textcenter, $height-13, $this->text, $black);

//		header('Content-type: image/png');
		imagepng($barcode);
		imagedestroy($barcode);
	}


	public function toDataUrl($height = 50, $widthScale = 1) {
		ob_start();
		$this->renderImage($height, $widthScale);
		$data = ob_get_contents();
		ob_end_clean();
		return 'data:image/png;base64,' . base64_encode($data);
	}


	public function toHtmlTag($height = 50, $widthScale = 1) {
		$dataUrl = $this->toDataUrl($height, $widthScale);
		echo "<img src=\"$dataUrl\" />";
	}
}

class BarcodeException extends Exception {

}
