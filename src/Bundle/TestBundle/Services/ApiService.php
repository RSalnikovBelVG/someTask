<?php
declare(strict_types=1);

namespace App\Bundle\TestBundle\Services;

abstract class ApiService
{
    /**
     * @var string
     */
    private $url;
    /**
     * @var null
     */
    private $auth;

    public function __construct(string $url, $auth = null)
    {
        $this->url = $url;
        $this->auth = $auth;
    }


    public function getData()
    {
        $auth = '';
        if ($this->auth) {
            $key = key($this->auth);
            $val = $this->auth[$key];
            $auth = "?$key=$val";
        }

        return json_decode(file_get_contents($this->url . $auth), true);
    }
}