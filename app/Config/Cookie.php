<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use DateTimeInterface;

class Cookie extends BaseConfig
{
    /**
     * Cookie Prefix
     * Must be a string, setting an empty string if not used
     */
    public string $prefix = ''; // Ensure this is a string

    /**
     * Cookie Expires Timestamp
     */
    public $expires = 0;

    /**
     * Cookie Path
     */
    public string $path = '/';

    /**
     * Cookie Domain
     */
    public string $domain = '';

    /**
     * Cookie Secure (HTTPS)
     */
    public bool $secure = false;

    /**
     * Cookie HTTPOnly
     */
    public bool $httponly = true;

    /**
     * Cookie SameSite Policy
     */
    public string $samesite = 'Lax';

    /**
     * Cookie Raw
     */
    public bool $raw = false;

    public function __construct()
    {
        // Ensure prefix is always a string
        if (!is_string($this->prefix)) {
            $this->prefix = ''; // Fixes `validatePrefix()` error
        }

        // Automatically detect environment for Secure & SameSite settings
        $isLocal = $_SERVER['HTTP_HOST'] === 'localhost' || str_contains($_SERVER['HTTP_HOST'], '127.0.0.1');
        $this->secure = !$isLocal;
        $this->samesite = $isLocal ? 'None' : 'Strict';

        if ($this->samesite === 'None') {
            $this->secure = true;
        }
    }
}
