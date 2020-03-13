<?php

class Admin{
    // property declaration
    public $username;
    public $password;


    public function __construct($username='', $password='') {
        $this->username = $username;
        $this->password = $password;
    }

    public function authenticate($enteredPwd) {
        return password_verify ($enteredPwd, $this->password);
    }

    public function getUsername(){
        return $this->username;
    }

    public function getPassword(){
        return $this->password;
    }


}