<?php

namespace Http;

class Response
{
    public function __construct()
    {
        return $this;
    }

    public function json(mixed $content, $status = '200')
    {
        header('Content-Type: application/json', true, $status);        
        echo json_encode($content);
    }
}