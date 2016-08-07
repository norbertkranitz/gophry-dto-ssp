<?php

namespace Gophry\Provider\DTO;

use \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class InvalidRequestException extends UnprocessableEntityHttpException {
    
    private $data;
    
    public function __construct($message, $data = array()) {
        parent::__construct($message);
        $this->data = $data;
    }
    
    public function getData() {
        return $this->data;
    }
    
}