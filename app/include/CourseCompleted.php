<?php

class CourseCompleted {
    // property declaration
    public $userid;
    public $code;
    
    public function __construct($userid='', $code='') {
        $this->userid = $userid;
        $this->code = $code;
    }

    public function getUserid(){
        return $this->userid;
    }

    public function getCode(){
        return $this->code;
    }
}
?>