<?php

namespace App\Helpers\Classes;

use Jenssegers\Agent\Agent;


class UserAgent
{
    private null|object $agent;

    public function __construct(
        $userAgent = null,
        $headers = null
    ) {
        $this->agent = new Agent();

        if (! empty($userAgent)) $this->agent->setUserAgent($userAgent);
        if (! empty($headers)) $this->agent->setHttpHeaders($headers);
    }

    public function browser()
    {
        return $this->agent->browser();
    }

    public function browserVersion()
    {
        return $this->agent->version($this->browser());
    }

    public function platform()
    {
        return $this->agent->platform();
    }

    public function platformVersion()
    {
        return $this->agent->version($this->platform());
    }

    public function device()
    {
        return $this->agent->device();
    }

    public function deviceType()
    {
        if($this->isMobile()) return "Mobile";
        if($this->isTablet()) return "Tablet";
        if($this->isDesktop()) return "Desktop";

        return "Unkown";
    }

    public function isMobile()
    {
        return $this->agent->isMobile();
    }

    public function isTablet()
    {
        return $this->agent->isTablet();
    }

    public function isDesktop()
    {
        return $this->agent->isDesktop();
    }
}