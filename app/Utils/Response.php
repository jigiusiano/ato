<?php

namespace App\Utils;

class Response
{
    public int $code;
    public string $message;
    public mixed $data;
    public string $areDataValid;

    public function __construct(){}
}