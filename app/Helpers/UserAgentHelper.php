<?php

use App\Helpers\Classes\UserAgent;

if(! function_exists('userAgent')) {
    function userAgent($userAgent = null, $headers = null)
    {
        return new UserAgent($userAgent, $headers);
    }
}


