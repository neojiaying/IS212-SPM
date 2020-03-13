<?php
require_once 'common.php';
require_once 'protectadmin.php';

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

    $bidded = $dao_bid->retrieveSpecificBid($one_section_course,$one_section_section);
    // var_dump($bidded);
    if ($bidded){
        if (sizeof($bidded) > $one_section_size || sizeof($bidded) == $one_section_size ){ // if bidded people > class size
            $clearing_price = $bidded[$one_section_size - 1] -> getAmount();
            // var_dump($clearing_price);     
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
                // var_dump($index);
                // var_dump($bidded_success);
                // var_dump($bidded_fail);
            }   
            else{ // If there is more than one person bidding the same clearing price amount
                $bidded_success = array_slice($bidded,0,$index[0]);//sql - status to Successful
                $bidded_dropped = array_slice($bidded,$index[0], sizeof($index)); //sql - status to Dropped
                $bidded_fail = array_slice($bidded,$index[sizeof($index) - 1] + 1); //sql - status to Un-Successful

            }  
            
        }

        else{ // if bidded people < class size
            $bidded_success = $bidded;  
        }

        
        
         // var_dump($bidded_success);

         $dao_result = new BidDAO();
 
        foreach ($bidded_success as $one_success){
            $status = "Success";
            $userid = $one_success->getUserid();
            // var_dump($userid);
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
             $final_balance = $bidded_amount + $balance; //cuz fail bid, need add bidded amount back
             $update_balance = $dao_result-> updateCredits($final_balance,$userid);
             
 
 
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
             $final_balance = $bidded_amount + $balance; //cuz fail bid, need add bidded amount back
             $update_balance = $dao_result-> updateCredits($final_balance,$userid);
             
 
 
 
         }
 
     }
     //Reset array after each section processed
    $bidded_success = []; 
    $bidded_fail = []; 
    $bidded_dropped=[];
     
 }
 
 $dao_endround = new RoundDAO();
 $round = $dao_endround->endround();
 
 if($round){
     $getround = $dao_endround->getDetails();
     $_SESSION['status'] = $getround[0]->getStatus();
     
 }
 
 
 header("Location: ../admin_index.php?msg=Bids Processed");
 return;
 
 
 ?>
