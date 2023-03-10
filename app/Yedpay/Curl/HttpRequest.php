<?php

namespace App\Yedpay\Curl;

interface HttpRequest
{
    public function setOptionArray($url, $method, $parameters, $token, $isAccessToken);

    public function execute();

    public function error();

    public function close();
}
