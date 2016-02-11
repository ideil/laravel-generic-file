<?php

namespace Ideil\LaravelGenericFile\Traits;

trait TokenTrait
{
    use \Ideil\GenericFile\Traits\HashingTrait;

    /**
     * Make token from string.
     *
     * @param string $str
     *
     * @return string
     */
    public function tokenFromStr($str)
    {
        return substr($this->str(env('APP_KEY').$str), 0, 32);
    }

    /**
     * Make token from string.
     *
     * @param string $str
     *
     * @return string
     */
    public function token6FromStr($str)
    {
        return substr($this->tokenFromStr($str), 0, 6);
    }
}
