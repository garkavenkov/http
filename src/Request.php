<?php

namespace Http;

class Request
{
    /**
     * Posted data
     *
     * @var array
     */
    private $posted_data = [];

    /**
     * Request method: POST, GET, PATCH, etc.
     *
     * @var string
     */
    private $method = '';

    /**
     * Content type
     *
     * @var string
     */
    private $type = '';

    /**
     * Request headers
     *
     * @var array
     */
    private $request_headers = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->type = $_SERVER['HTTP_CONTENT_TYPE'];
               
        $this->request_headers = array_intersect_key(
                $_SERVER,
                array_flip(
                    array_filter(
                        array_keys($_SERVER), function($key) {
                            return str_starts_with($key, 'HTTP');
                        }
                    )
                )
        );       

        // Takes raw data from the request
        $this->posted_data = [];
        if ($this->type == 'application/json') {            
            $json = file_get_contents('php://input');
            $this->posted_data = json_decode($json, true);            
            
        } else {
            $this->posted_data = $_POST;
        }        
        
    }   

    /**
     * Setter for posted data
     *
     * @param string $name  Parameter's name
     * @param mixed $value  Parameter's value
     */
    public function __set(string $name, mixed $value)
    {
        $this->posted_data[$name] = $value;        
    }

    /**
     * Getter for posted data
     *
     * @param string $name  Parameter's name
     * @return mixed $value
     */
    public function __get(string $name)
    {
        if (isset($this->posted_data[$name])) {
            return $this->posted_data[$name];
        }
        return null;
    }

    /**
     * Get posted parameter by name, or if not specified
     * return all posted parameters
     *
     * @param string $name  Parameter's name
     * @return mixed
     */
    public function input(string $name = ''): mixed
    {
        if ($name !== '') {
            if (array_key_exists($name, $this->posted_data)) {
                return $this->posted_data[$name];
            }
        } else {                                  
            return $this->posted_data;
        }
        return null;
    }

    /**
     * Return request method (POST, GET, etc.)     
     *     
     * @return string   Request method
     */
    public function method()
    {        
        return $this->method;     
    }

    /**
     * Check current request method with given $method
     *
     * @param string $method    Method (POST, GET, etc.)
     * @return boolean
     */
    public function isMethod(string $method): bool
    {
        return $this->method === $method;
    }

    /**
     * Return request's header by name, or if not specified
     * return all request headers
     *
     * @param string $header    Header name
     * @return mixed            Request's header
     */
    public function headers(string $header = ''): mixed
    {
        if ($header !== '') {
            $header = strtoupper($header);
            if (!str_starts_with('HTTP_', $header)) {
                $header = 'HTTP_' . $header;                
            }            
            if (array_key_exists($header, $this->request_headers)) {
                return $this->request_headers[$header];
            } 
            return null;
        } else {
            return $this->request_headers;
        }
    }

}
