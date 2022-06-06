<?php

namespace App\Helpers;

use League\ISO3166\ISO3166;

class CountryCodes
{
    public static function alpha2To3($code)
    {
        return (new ISO3166)->alpha2($code)['alpha3'];
    }
}