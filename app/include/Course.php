<?php

class Course {
    // property declaration
    public $course;
    public $school;
    public $title;    
    public $description;
    public $examdate;
    public $examstart;
    public $examend;
    
    public function __construct($course='', $school='', $title='', $description='',$examdate='',$examstart='',$examend='') {
        $this->course = $course;
        $this->school = $school;
        $this->title = $title;
        $this->description = $description;
        $this->examdate = $examdate;
        $this->examstart = $examstart;
        $this->examend = $examend;
    }

    public function getCourse() {
        return $this->course; 
    }

    public function getSchool() {
        return $this->school; 
    }

    public function getTitle() {
        return $this->title; 
    }
    
    public function getDescription() {
        return $this->description; 
    }  

    public function getExamdate() {
        return $this->examdate; 
    } 
    
    public function getExamstart() {
        return $this->examstart; 
    } 

    public function getExamend() {
        return $this->examend; 
    } 

    public function setExamstart($examstart) {
        $this->examstart = $examstart;
    }
    public function setExamend($examend) {
        $this->examend = $examend;
    }
    
}

?>