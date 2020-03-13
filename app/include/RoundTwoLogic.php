<?php
require_once 'common.php';
class RoundTwoLogic{
    //RoundTwoLogic contains all the logical validations required that are specific to Round 2

    public function checkbids($userid){
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        $sql = "SELECT * FROM bid WHERE userid = :userid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam('userid', $userid, PDO::PARAM_STR);
        $stmt->execute();
        $result = [];
        while ($row = $stmt->fetch()){
            $result[] = new Bid($row['userid'], $row['amount'], $row['code'], $row['section'], $row['status'], $row['round']);
        }
        return $result;
    }

    public function processbids(){
        $dao_section = new SectionDAO();
        $sections = $dao_section->retrieveAll();
        $dao_bid = new BidDAO();

        $bidded_success = []; //stores an array of objects for people who sucessfully bid for the course
        $bidded_fail = []; //stores an array of objects for people who fail the bid for the course
        $bidded_dropped=[];

        foreach($sections as $one_section){
            $one_section_course = $one_section->getCourse();
            $one_section_section = $one_section->getSection();
            $one_section_size = $one_section->getSize();
            
            $bidded = $dao_bid->retrieveSpecificBidRoundTwo($one_section_course,$one_section_section);
            // var_dump($bidded);
            if ($bidded){
                $bidded_success = [];
                $bidded_dropped = [];
                $bidded_fail = [];
                if (sizeof($bidded) <= $one_section_size ){ // if bidded people > class size
                    $bidded_success = $bidded;  
                    
                    // var_dump($clearing_price);       
                }

                else{ // if bidded people < class size
                    $clearing_price = $bidded[$one_section_size - 1] -> getAmount();
                    $count = -1;
                    $slice = 0;
                    $index = []; // stores the index of the people who bid the same amount as the clearing price
                    foreach($bidded as $one_bidded){
                        $count++;
                        $amount = $one_bidded -> getAmount(); 
                        
                
                        if ( $amount == $clearing_price){

                            $slice = $count;

                            $index[]= $slice;

                        }
                    
                    }

                    if (count($index) == 1){ //if only 1 perosn bid for the clearing price amount

                        $bidded_success = array_slice($bidded,0,$index[0] + 1); //sql - status to Successful
                        $bidded_fail = array_slice($bidded,$index[0] + 1); // sql - status to Un-Successful
                    }   
                    else{ // If there is more than one person bidding the same clearing price amount
                        $bidded_success = array_slice($bidded,0,$index[0]);//sql - status to Successful
                        $bidded_dropped = array_slice($bidded,$index[0], sizeof($index)); //sql - status to Dropped
                        $bidded_fail = array_slice($bidded,$index[sizeof($index) - 1] + 1); //sql - status to Un-Successful

                    }
                }

                
                // var_dump($bidded_success);

                $dao_result = new BidDAO();
                $status="";
        
                foreach ($bidded_success as $one_success){
                    $status = "Success";
                    $userid = $one_success->getUserid();
                    $update_success = $dao_result->updateStatus($status,$userid,$one_section_course,$one_section_section,$_SESSION['round']);
        
                }

                $dao_student = new StudentDAO();
        
                foreach ($bidded_dropped as $one_dropped){
                    $status = "Fail";
                    $userid = $one_dropped->getUserid();
                    $update_dropped = $dao_result->updateStatus($status,$userid,$one_section_course,$one_section_section,$_SESSION['round']);
                    $bidded_amount = $one_dropped->getAmount(); //amt user bidded for
                    $student = $dao_student->retrieve($userid);
                    $balance = $student->getEdollar(); //student balance after placing bids
                    if ($_SESSION['round'] == 'inactive'){//Should only refund at end of round
                        $final_balance = $bidded_amount + $balance; //cuz fail bid, need add bidded amount back
                        $update_balance = $dao_result-> updateCredits($final_balance,$userid);
                    }
                    
        
        
                }
                // var_dump($bidded_fail);
        
                //  var_dump($bidded_success);
                //  var_dump($bidded_dropped);
                //  var_dump($bidded_fail);
                
                
        
                foreach ($bidded_fail as $one_fail){
                    $status = "Fail";
                    $userid = $one_fail->getUserid();
                    $update_fail = $dao_result->updateStatus($status,$userid,$one_section_course,$one_section_section,$_SESSION['round']);
                    $bidded_amount = $one_fail->getAmount(); //amt user bidded for
                    $student = $dao_student->retrieve($userid);
                    $balance = $student->getEdollar(); //student balance after placing bids
                    if ($_SESSION['round'] == 'inactive'){//Should only refund at end of round
                        $final_balance = $bidded_amount + $balance; //cuz fail bid, need add bidded amount back
                        $update_balance = $dao_result-> updateCredits($final_balance,$userid);
                    }
                    
        
        
                
                }
                $minbidDAO = new MinBidDAO();
                $minimum_bid = (float)($minbidDAO->retrieve($one_section_course, $one_section_section)[0]->getMinBid());
                $sql = "SELECT * FROM bid WHERE code = :code and section = :section and status = 'success' and round = 2 ORDER BY amount DESC";
                $connMgr = new ConnectionManager();      
                $conn = $connMgr->getConnection();
                $stmt = $conn->prepare($sql);
                $code = $one_section_course;
                $section = $one_section_section;
                $stmt->bindParam(':code', $code, PDO::PARAM_STR);
                $stmt->bindParam(':section', $section, PDO::PARAM_STR);
                if ($stmt->execute()){
                    $result = [];
                    while ($row = $stmt->fetch()){
                        $result[] = new Bid($row['userid'], $row['amount'], $row['code'], $row['section'], $row['status'], $row['round']);
                    }             
                }
                $sql1 = "SELECT * FROM bid WHERE code = :code and section = :section and status = 'fail' and round = 2 ORDER BY amount DESC";
                $stmt1 = $conn->prepare($sql1);
                $stmt1->bindParam(':code', $code, PDO::PARAM_STR);
                $stmt1->bindParam(':section', $section, PDO::PARAM_STR);
                if ($stmt1->execute()){
                $result1 = [];
                    while ($row = $stmt1->fetch()){
                        $result1[] = new Bid($row['userid'], $row['amount'], $row['code'], $row['section'], $row['status'], $row['round']);
                    }
                }
                if ($result != []){
                    if (sizeof($result) + sizeof($result1) >= $one_section_size){
                        if (sizeof($result) >= $one_section_size){
                            $check_price = $result[$one_section_size - 1]->getAmount();
                        }
                        else{
                            $check_price = $result1[$one_section_size - sizeof($result) - 1]->getAmount();
                        }
                        if ((float)$check_price >= $minimum_bid){
                            $minbidDAO->update($one_section_course, $one_section_section, (float)$check_price + 1);
                        }
                    }
                }
        
            }
            
        }
        return;
        
    }
    public function processbidsreturnmin(){
        $dao_section = new SectionDAO();
        $sections = $dao_section->retrieveAll();
        $dao_bid = new BidDAO();

        $bidded_success = []; //stores an array of objects for people who sucessfully bid for the course
        $bidded_fail = []; //stores an array of objects for people who fail the bid for the course
        $bidded_dropped=[];

        foreach($sections as $one_section){
            $one_section_course = $one_section->getCourse();
            $one_section_section = $one_section->getSection();
            $one_section_size = $one_section->getSize();
            $clearing_price = 10;
            $bidded = $dao_bid->retrieveSpecificBidRoundTwo($one_section_course,$one_section_section);
            // var_dump($bidded);
            if ($bidded){
                $bidded_success = [];
                $bidded_dropped = [];
                $bidded_fail = [];
                if (sizeof($bidded) < $one_section_size ){ // if bidded people > class size
                    $bidded_success = $bidded;  
                    return $clearing_price;
                    // var_dump($clearing_price);       
                }
                elseif (sizeof($bidded) == $one_section_size){
                    $bidded_success = $bidded;  
                    $clearing_price = $bidded[$one_section_size - 1]->getAmount() + 1;
                    return $clearing_price;
                }

                else{ // if bidded people < class size
                    $clearing_price = $bidded[$one_section_size - 1] -> getAmount() + 1;
                    return $clearing_price;
                    $count = -1;
                    $slice = 0;
                    $index = []; // stores the index of the people who bid the same amount as the clearing price
                    foreach($bidded as $one_bidded){
                        $count++;
                        $amount = $one_bidded -> getAmount(); 
                        
                
                        if ( $amount == $clearing_price){

                            $slice = $count;

                            $index[]= $slice;

                        }
                    
                    }

                    if (count($index) == 1){ //if only 1 perosn bid for the clearing price amount

                        $bidded_success = array_slice($bidded,0,$index[0] + 1); //sql - status to Successful
                        $bidded_fail = array_slice($bidded,$index[0] + 1); // sql - status to Un-Successful
                    }   
                    else{ // If there is more than one person bidding the same clearing price amount
                        $bidded_success = array_slice($bidded,0,$index[0]);//sql - status to Successful
                        $bidded_dropped = array_slice($bidded,$index[0], sizeof($index)); //sql - status to Dropped
                        $bidded_fail = array_slice($bidded,$index[sizeof($index) - 1] + 1); //sql - status to Un-Successful

                    }
                }

                
                // var_dump($bidded_success);

                $dao_result = new BidDAO();
                $status="";
        
                foreach ($bidded_success as $one_success){
                    $status = "Success";
                    $userid = $one_success->getUserid();
                    $update_success = $dao_result->updateStatus($status,$userid,$one_section_course,$one_section_section,$_SESSION['round']);
        
                }
                // $numsuccessbids = sizeof($bidded_success);
                // $dao_section->updateSectionSize($numsuccessbids,$one_section_course,$one_section_section,$one_section_size);
                // var_dump($bidded_dropped);
                $dao_student = new StudentDAO();
        
                foreach ($bidded_dropped as $one_dropped){
                    $status = "Fail";
                    $userid = $one_dropped->getUserid();
                    $update_dropped = $dao_result->updateStatus($status,$userid,$one_section_course,$one_section_section,$_SESSION['round']);
                    $bidded_amount = $one_dropped->getAmount(); //amt user bidded for
                    $student = $dao_student->retrieve($userid);
                    $balance = $student->getEdollar(); //student balance after placing bids
                    if ($_SESSION['round'] == 'inactive'){//Should only refund at end of round
                        $final_balance = $bidded_amount + $balance; //cuz fail bid, need add bidded amount back
                        $update_balance = $dao_result-> updateCredits($final_balance,$userid);
                    }
                    
        
        
                }
                // var_dump($bidded_fail);
        
                //  var_dump($bidded_success);
                //  var_dump($bidded_dropped);
                //  var_dump($bidded_fail);
                
                
        
                foreach ($bidded_fail as $one_fail){
                    $status = "Fail";
                    $userid = $one_fail->getUserid();
                    $update_fail = $dao_result->updateStatus($status,$userid,$one_section_course,$one_section_section,$_SESSION['round']);
                    $bidded_amount = $one_fail->getAmount(); //amt user bidded for
                    $student = $dao_student->retrieve($userid);
                    $balance = $student->getEdollar(); //student balance after placing bids
                    if ($_SESSION['round'] == 'inactive'){//Should only refund at end of round
                        $final_balance = $bidded_amount + $balance; //cuz fail bid, need add bidded amount back
                        $update_balance = $dao_result-> updateCredits($final_balance,$userid);
                    }
                    
        
        
                
                }
        
            }
            
        }        
        return $clearing_price;
    }

    public function processbidsEndRound2(){
        $dao_section = new SectionDAO();
        $sections = $dao_section->retrieveAll();
        $dao_bid = new BidDAO();

        $bidded_success = []; //stores an array of objects for people who sucessfully bid for the course
        $bidded_fail = []; //stores an array of objects for people who fail the bid for the course
        $bidded_dropped=[];

        foreach($sections as $one_section){
            $one_section_course = $one_section->getCourse();
            $one_section_section = $one_section->getSection();
            $one_section_size = $one_section->getSize();
            $clearing_price = 10;
            $bidded = $dao_bid->retrieveSpecificBidRoundTwo($one_section_course,$one_section_section);
            // var_dump($bidded);
            if ($bidded){
                $bidded_success = [];
                $bidded_dropped = [];
                $bidded_fail = [];
                if (sizeof($bidded) <= $one_section_size ){ // if bidded people > class size
                    $bidded_success = $bidded;  
                    
                    // var_dump($clearing_price);       
                }

                else{ // if bidded people < class size
                    $clearing_price = $bidded[$one_section_size - 1] -> getAmount();
                    $count = -1;
                    $slice = 0;
                    $index = []; // stores the index of the people who bid the same amount as the clearing price
                    foreach($bidded as $one_bidded){
                        $count++;
                        $amount = $one_bidded -> getAmount(); 
                        
                
                        if ( $amount == $clearing_price){

                            $slice = $count;

                            $index[]= $slice;

                        }
                    
                    }

                    if (count($index) == 1){ //if only 1 perosn bid for the clearing price amount

                        $bidded_success = array_slice($bidded,0,$index[0] + 1); //sql - status to Successful
                        $bidded_fail = array_slice($bidded,$index[0] + 1); // sql - status to Un-Successful
                    }   
                    else{ // If there is more than one person bidding the same clearing price amount
                        $bidded_success = array_slice($bidded,0,$index[0]);//sql - status to Successful
                        $bidded_dropped = array_slice($bidded,$index[0], sizeof($index)); //sql - status to Dropped
                        $bidded_fail = array_slice($bidded,$index[sizeof($index) - 1] + 1); //sql - status to Un-Successful

                    }
                }

                
                // var_dump($bidded_success);

                $dao_result = new BidDAO();
                $status="";
        
                foreach ($bidded_success as $one_success){
                    $status = "Success";
                    $userid = $one_success->getUserid();
                    $update_success = $dao_result->updateStatus($status,$userid,$one_section_course,$one_section_section,$_SESSION['round']);
        
                }
                $numsuccessbids = sizeof($bidded_success);
                $dao_section->updateSectionSize($numsuccessbids,$one_section_course,$one_section_section,$one_section_size);
                // var_dump($bidded_dropped);
                $dao_student = new StudentDAO();
        
                foreach ($bidded_dropped as $one_dropped){
                    $status = "Fail";
                    $userid = $one_dropped->getUserid();
                    $update_dropped = $dao_result->updateStatus($status,$userid,$one_section_course,$one_section_section,$_SESSION['round']);
                    $bidded_amount = $one_dropped->getAmount(); //amt user bidded for
                    $student = $dao_student->retrieve($userid);
                    $balance = $student->getEdollar(); //student balance after placing bids
                    if ($_SESSION['round'] == 'inactive'){//Should only refund at end of round
                        $final_balance = $bidded_amount + $balance; //cuz fail bid, need add bidded amount back
                        $update_balance = $dao_result-> updateCredits($final_balance,$userid);
                    }
                    
        
        
                }
                // var_dump($bidded_fail);
        
                //  var_dump($bidded_success);
                //  var_dump($bidded_dropped);
                //  var_dump($bidded_fail);
                
                
        
                foreach ($bidded_fail as $one_fail){
                    $status = "Fail";
                    $userid = $one_fail->getUserid();
                    $update_fail = $dao_result->updateStatus($status,$userid,$one_section_course,$one_section_section,$_SESSION['round']);
                    $bidded_amount = $one_fail->getAmount(); //amt user bidded for
                    $student = $dao_student->retrieve($userid);
                    $balance = $student->getEdollar(); //student balance after placing bids
                    if ($_SESSION['round'] == 'inactive'){//Should only refund at end of round
                        $final_balance = $bidded_amount + $balance; //cuz fail bid, need add bidded amount back
                        $update_balance = $dao_result-> updateCredits($final_balance,$userid);
                    }
                    
        
        
                
                }
        
            }
            
        }
        return;
        
    }
}

?>