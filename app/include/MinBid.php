<?php
require_once 'common.php';
class MinBid{
    public $course;
    public $section;
    public $minbid;

    public function __construct($course='',$section='',$minbid=''){
        $this->course = $course;
        $this->section = $section;
        $this->minbid = $minbid;
    }

    public function getCourse(){
        return $this->course;
    }

    public function getSection(){
        return $this->section;
    }

    public function getMinBid(){
        return $this->minbid;
    }
}