<?php

class StudentDAO {
    
    public  function retrieve($userid) {
        //Retrieve students based on userid
        $sql = 'select * from student where userid=:userid';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
            
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->execute();


        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new Student($row['userid'], $row['password'], $row['name'], $row['school'], $row['edollar']);
        }
    }

    public  function retrieveAll() {
        $sql = 'SELECT * from student ORDER by userid';
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();


        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Student($row['userid'], $row['password'], $row['name'], $row['school'], $row['edollar']);
        }
        return $result;
    }

    function validation($userid, $password, $name, $edollar){
        $errors = [];

        if (strlen($userid) > 128){
            $errors[] = "invalid userid";
        }

        //duplicate check
        $sql = "SELECT * FROM student where userid = :userid";

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        if ($stmt->execute()){
            $count = 0;
            while ($row =  $stmt->fetch()){
                $count++;
            }
            if ($count >= 1){
                $errors[] = "duplicate userid";
            }
        }
        if (!is_numeric($edollar) || $edollar <0){
            $errors[] = "invalid e-dollar";
        }
        else{
            //Explode to compare numerical values
            $edollar_array = explode(".", $edollar);
            if ((int)$edollar < 0 || sizeof($edollar_array) > 2 || (sizeof($edollar_array) == 2 && strlen($edollar_array[1])  > 2) || $edollar_array[0] < 0){ //check if there is decimal place. If there is, check if less or equal to 2dp
                $errors[] = "invalid e-dollar";
            }
        }

        if (strlen($password) > 128){
            $errors[] = "invalid password";
        }
        
        if (strlen($name) > 100){
            $errors[] = "invalid name";
        }

        return $errors;
    }

    public function add($student) {
        $sql = "INSERT INTO student (userid, password, name, school, edollar) VALUES (:userid, :password, :name, :school, :edollar)";

        $is_blank = [];
        if (empty($student->getUserid())){
            $is_blank[] = "blank userid";
        }
        if (empty($student->getPassword())){
            $is_blank[] = "blank password";
        }
        if (empty($student->getName())){
            $is_blank[] = "blank name";
        }
        if (empty($student->getSchool())){
            $is_blank[] = "blank school";
        }
        if ($student->getEdollar() == ''){
            $is_blank[] = "blank edollar";
        }
        if ($is_blank != []){
            return $is_blank;
        }

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);    
        
        $userid = trim($student->userid);
        $password = $student->password; //Password should not be trimmed
        $name = trim($student->name);
        $school = trim($student->school);
        $edollar = trim($student->edollar);
        $errors = $this->validation($userid, $password, $name, $edollar);

        if ($errors != []){
            return $errors;
        }

        // $password = password_hash($student->password,PASSWORD_DEFAULT);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':school', $school, PDO::PARAM_STR);
        $stmt->bindParam(':edollar', $edollar, PDO::PARAM_STR);

        $isAddOK = False;
        
        if ($stmt->execute()) {
            $isAddOK = True;
        }

        return $isAddOK;
    }

     public function update($user) {
        $sql = 'UPDATE student SET gender=:gender, password=:password, name=:name WHERE username=:username';      
        
        $connMgr = new ConnectionManager();           
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        
        // $user->password = password_hash($user->password,PASSWORD_DEFAULT);

        $stmt->bindParam(':username', $user->username, PDO::PARAM_STR);
        $stmt->bindParam(':gender', $user->gender, PDO::PARAM_STR);
        $stmt->bindParam(':password', $user->password, PDO::PARAM_STR);
        $stmt->bindParam(':name', $user->name, PDO::PARAM_STR);

        $isUpdateOk = False;
        if ($stmt->execute()) {
            $isUpdateOk = True;
        }

        return $isUpdateOk;
    }
	
	public function removeAll() {
        $sql = 'TRUNCATE TABLE student;';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    }  

 
    
    



	
}


