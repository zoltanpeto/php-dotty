<?php
/**
 * @author John Smart
 */

namespace dotty;

/**
 * Access arrays quickly using dot-notation.
 */
class Dotty {

	private function __construct() {
	}

	/**
	 * @throws \InvalidArgumentException
	 *
	 * @param string $notation		Dot notation
	 * @param array& $dataCursor	Data to search through
	 * @return mixed&	A reference to the data
	 */
	public static function &dot($notation, array &$data) {
		$dataCursor		=& $data;

		$instructions	= array();
		$symbols		= explode('.', $notation);
		foreach ($symbols as $symbol) {
			if (preg_match('/^(.*)\[([\d]+)\]$/', $symbol, $matches)) {
				if (!empty($matches[1])) {
					$instructions[]	= $matches[1]; // symbol
				}
				$instructions[]	= (int)$matches[2]; // offset
			} else {
				$instructions[]	= $symbol;
			}
		}

		$pathSoFar	= array();
		foreach ($instructions as $x) {
			if (is_int($x)) {
				if (count($pathSoFar)) {
					$pathSoFar[count($pathSoFar) - 1] .= "[{$x}]";
				} else {
					$pathSoFar[]	= "[{$x}]";
				}
			} else {
				$pathSoFar[] = $x;
			}

			if (array_key_exists($x, $dataCursor)) {
				$dataCursor =& $dataCursor[$x];
			} else {
				throw new \InvalidArgumentException(sprintf('"%s" does not exist', implode('.', $pathSoFar)));
			}
		}

		return $dataCursor;
	}
}