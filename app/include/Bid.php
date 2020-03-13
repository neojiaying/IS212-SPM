<?php

class Bid {
    // property declaration
    public $userid;
    public $amount;
    public $code;    
    public $section;
    public $status;
    public $round;
    
    public function __construct($userid='', $amount='', $code='', $section='', $status='pending', $round='1') {
        $this->userid = $userid;
        $this->amount = $amount;
        $this->code = $code;
        $this->section = $section;
        $this->status = $status;
        $this->round = $round;
    }
    
    public function getUserid() {
        return $this->userid; 
    }
    public function getAmount() {
        return $this->amount; 
    }
    public function getCode() {
        return $this->code; 
    }
    public function getSection() {
        return $this->section; 
    }   
    public function getStatus() {
        return $this->status;
    }
    public function getRound() {
        return $this->round;
    }
    
}

?>