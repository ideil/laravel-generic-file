<?php namespace Ideil\LaravelGenericFile\Traits;

trait TokenTrait {

	use \Ideil\GenericFile\Traits\HashingTrait;

	/**
	 * Make token from string.
	 *
	 * @param  string $str
	 * @return string
	 */
	public function tokenFromStr($str)
	{
		return $this->str(env('APP_KEY') . $str, false);
	}

	/**
	 * Make token from string.
	 *
	 * @param  string $str
	 * @return string
	 */
	public function token6FromStr($str)
	{
		return substr($this->tokenFromStr($str), 0, 6);
	}

}
