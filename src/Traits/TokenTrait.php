<?php namespace Ideil\LaravelGenericFile\Traits;

trait TokenTrait {

	use \Ideil\GenericFile\Traits\HashingTrait;

	/**
	 * Make token from string.
	 *
	 * @param  string $str
	 * @return string
	 */
	public function tokenFormStr($str)
	{
		return $this->str(env('APP_KEY') . $str, false);
	}

	/**
	 * Make token from string.
	 *
	 * @param  string $str
	 * @return string
	 */
	public function token6FormStr($str)
	{
		return substr($this->tokenFormStr($str), 0, 6);
	}

}
