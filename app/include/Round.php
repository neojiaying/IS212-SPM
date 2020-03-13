<?php

class Round {
    //Round stores the current round and the round status
    public $round;
    public $status;

    public function __construct($round='', $status=''){
        $this->round = $round;
        $this->status = $status;
    }

    public function getRound(){
        return $this->round;
    }

    public function getStatus(){
        return $this->status;
    }
}