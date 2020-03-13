<?php
require_once 'common.php';

function doBootstrap() {
		

	$errors = array();
	# need tmp_name -a temporary name create for the file and stored inside apache temporary folder- for proper read address
	$zip_file = $_FILES["bootstrap-file"]["tmp_name"];
	// var_dump($_FILES);
	// var_dump($REQEUST);

	# Get temp dir on system for uploading
	$temp_dir = sys_get_temp_dir();

	# keep track of number of lines successfully processed for each file
    $prerequisite_processed=0;
    $bid_processed=0;
    $section_processed=0;
    $course_processed=0;
    $student_processed=0;
    $course_completed_processed=0;

	# check file size
	if ($_FILES["bootstrap-file"]["size"] <= 0){
		$errors[] = "input files not found";
		
	}

	else {
		
		$zip = new ZipArchive;
		$res = $zip->open($zip_file);
		// echo $zip->numFiles;
		//Check if correct number of files are processed
		if ($zip->numFiles < 6){
			$errors[] = "input files not found";
		}

		elseif ($res === TRUE) {
			$zip->extractTo($temp_dir);
			$zip->close();
		
			$prerequisite_path = "$temp_dir/prerequisite.csv";
			$bid_path = "$temp_dir/bid.csv";
            $section_path = "$temp_dir/section.csv";
            $course_path = "$temp_dir/course.csv";
            $student_path = "$temp_dir/student.csv";
            $course_completed_path = "$temp_dir/course_completed.csv";
            
			
			$prerequisite = @fopen($prerequisite_path, "r");
			$bid = @fopen($bid_path, "r");
            $section = @fopen($section_path, "r");
            $course = @fopen($course_path, "r");
            $student = @fopen($student_path, "r");
            $course_completed = @fopen($course_completed_path, "r");
			// var_dump($prerequisite);
			// var_dump($bid);

			//If any files are missing or empty
			if (!isset($prerequisite) || !isset($bid) ||!isset($section) ||!isset($course) ||!isset($student) || empty($prerequisite) || empty($bid) || empty($section) || empty($course)|| empty($student)|| empty($course_completed)){
				$errors[] = "input files not found";
				if (!empty($prerequisite)){
					fclose($prerequisite);
					@unlink($prerequisite);
				} 
				
				if (!empty($bid)) {
					fclose($bid);
					@unlink($bid);
				}
				
				if (!empty($section)) {
					fclose($section);
					@unlink($section);
                }

                if (!empty($course)) {
					fclose($course);
					@unlink($course);
                }
                
                if (!empty($student)) {
					fclose($student);
					@unlink($student);
                }
                
                if (!empty($course_completed)) {
					fclose($course_completed);
					@unlink($course_completed);
				}
				
				
			}
			else {
				$connMgr = new ConnectionManager();
				$conn = $connMgr->getConnection();

				# start processing
				
				# truncate current SQL tables
				$course_completedDAO=new CourseCompletedDAO();
				$course_completedDAO->removeAll();

				$studentDAO=new StudentDAO();
				$studentDAO->removeAll();

				$courseDAO=new CourseDAO();
				$courseDAO->removeAll();

				$sectionDAO=new SectionDAO();
				$sectionDAO->removeAll();

				$bidDAO=new BidDAO();
                $bidDAO->removeAll();

				$prerequisiteDAO=new PrerequisiteDAO();
                $prerequisiteDAO->removeAll();
				
				$minbidDAO=new MinBidDAO();
                $minbidDAO->removeAll();
				# then read each csv file line by line (remember to skip the header)
				# $data = fgetcsv($file) gets you the next line of the CSV file which will be stored 
				# in the array $data
				# $data[0] is the first element in the csv row, $data[1] is the 2nd, ....
				
				# process each line and check for errors

				# for the project, the full error list is listed in the wiki

				// Course
				$data=fgetcsv($course);
				$error_line = 2;//2 because course_processed starts from 0, and since the first line is the categories, errors start form line 2 onwards.
				while (($data=fgetcsv($course))!==false){
					$data = array_map("utf8_encode", $data);
					$courseObj = new Course($data[0],$data[1],$data[2],$data[3],$data[4],$data[5],$data[6]);
					$course_result = $courseDAO->add($courseObj); 
					//Check for errors
					if (is_array($course_result)){
						$errors[] = ["file" => "course.csv", "line" => $error_line, "message" => $course_result];
					}
					elseif ($course_result == true){
						//if the line was added to the database
						$course_processed++;
					}
					$error_line++;
				}
				// var_dump($errors);

				// process each line, check for errors, then insert if no errors

				// clean up
				fclose($course);
				@unlink($course);

				// Section
				$data=fgetcsv($section);
				$error_line = 2;
				while (($data=fgetcsv($section))!==false){
					$data = array_map("utf8_encode", $data);
					$sectionObj = new Section($data[0],$data[1],$data[2],$data[3],$data[4],$data[5],$data[6],$data[7]);
					$section_result = $sectionDAO->add($sectionObj);
					//Check for errors
					if (is_array($section_result)){
						$errors[] = ["file" => "section.csv", "line" => $error_line, "message" => $section_result];
					}
					elseif ($section_result == true){
						//If the line was added to the database
						$section_processed++;
						$minbidDAO->add($data[0],$data[1]);//Data 0 is course, data 1 is section
					}
					$error_line++;
				}

				// process each line, check for errors, then insert if no errors

				// clean up
				fclose($section);
                @unlink($section);
                
                
				// Student
				$data=fgetcsv($student);
				$error_line = 2;
				while (($data=fgetcsv($student))!==false){
					$data = array_map("utf8_encode", $data);
					$studentObj = new Student($data[0],$data[1],$data[2],$data[3],$data[4]);
					$student_result = $studentDAO->add($studentObj);
					//Check for errors
					if (is_array($student_result)){
						$errors[] = ["file" => "student.csv", "line" => $error_line, "message" => $student_result];
					}
					elseif ($course_result == true){
						//If the line was added to the database
						$student_processed++;
					}
					$error_line++;
				}

				// process each line, check for errors, then insert if no errors

				// clean up
				fclose($student);
                @unlink($student);
                
				
				// Prerequisite

				// process each line, check for errors, then insert if no errors
				$data=fgetcsv($prerequisite);
				$error_line = 2;
				while (($data=fgetcsv($prerequisite))!==false){
					$data = array_map("utf8_encode", $data);
                    $prerequisiteObj = new Prerequisite ($data[0],$data[1]);
					$prerequisite_result = $prerequisiteDAO->add($prerequisiteObj);
					//Check for errors
					if (is_array($prerequisite_result)){
						$errors[] = ["file" => "prerequisite.csv", "line" => $error_line, "message" => $prerequisite_result];
					}
					elseif ($prerequisite_result == true){
						//If the ilne was added to the database
						$prerequisite_processed++;
					}
					$error_line++;
				}
				
				// Course Completed
				$data=fgetcsv($course_completed);
				$error_line = 2;
				while (($data=fgetcsv($course_completed))!==false){
					$data = array_map("utf8_encode", $data);
					$course_completedObj = new CourseCompleted($data[0],$data[1]);
					$course_completed_result = $course_completedDAO->add($course_completedObj);
					//Check for errors
					if (is_array($course_completed_result)){
						$errors[] = ["file" => "course_completed.csv", "line" => $error_line, "message" => $course_completed_result];
					}
					elseif ($course_completed_result == true){
						//If the line was added to the database
						$course_completed_processed++;
					}
					$error_line++;
				}

				// process each line, check for errors, then insert if no errors

				// clean up
				fclose($course_completed);
				@unlink($course_completed);

				// clean up
				fclose($prerequisite);
				@unlink($prerequisite);

				// bid
				$data=fgetcsv($bid);
				$error_line = 2;
				while (($data=fgetcsv($bid))!==false){
					$data = array_map("utf8_encode", $data);
					$bidObj= new Bid($data[0],$data[1],$data[2],$data[3]);
					$_SESSION['round'] = 1;
					$bid_result = $bidDAO->add($bidObj, $_SESSION['round']);
					//Check for errors
					if (is_array($bid_result)){
						$errors[] = ["file" => "bid.csv", "line" => $error_line, "message" => $bid_result];
					}
					elseif ($bid_result == true){
						//IF the line was added to the database
						$bid_processed++;
					}
					$error_line++;
				}

				// process each line, check for errors, then insert if no errors

				// clean up
				fclose($bid);
				@unlink($bid);

			}
		}
	}

	# Sample code for returning JSON format errors. remember this is only for the JSON API. Humans should not get JSON errors.

	if (!isEmpty($errors))
	{	
		$sortclass = new Sort();
		// $errors = $sortclass->sort_it($errors,"bootstrap");
		@array_multisort( 
			array_column($errors, 'file'), SORT_ASC,
			array_column($errors, 'line'), SORT_ASC,
			$errors
		  );
		$result = [ 
			"status" => "error",
			"num-record-loaded" => [
				["bid.csv" => $bid_processed],
				["course.csv" => $course_processed],
				["course_completed.csv" => $course_completed_processed],
				["prerequisite.csv" => $prerequisite_processed],
				["section.csv" => $section_processed],
				["student.csv" => $student_processed]
				
				
				
			],
			"error" => $errors
			// "messages" => $errors
		];
	}

	else
	{	
		$result = [ 
			"status" => "success",
			"num-record-loaded" => [
				["bid.csv" => $bid_processed],
				["course.csv" => $course_processed],
				["course_completed.csv" => $course_completed_processed],
				["prerequisite.csv" => $prerequisite_processed],
				["section.csv" => $section_processed],
				["student.csv" => $student_processed]
                
			]
		];
	}
	//Start round 1
	$RoundDAO = new RoundDAO();
	$roundresult = $RoundDAO->startRoundOne();
	if ($roundresult){
		$getround = $RoundDAO->getDetails();
		$_SESSION['round'] = $getround[0]->getRound();
		$_SESSION['status'] = $getround[0]->getStatus();
	}
	//For display purposes
	$_POST['errors'] = $errors;
	$_POST['bid_processed'] = $bid_processed;
	$_POST['course_processed'] = $course_processed;
	$_POST['course_completed_processed'] = $course_completed_processed;
	$_POST['prerequisite_processed'] = $prerequisite_processed;
	$_POST['section_processed'] = $section_processed;
	$_POST['student_processed'] = $student_processed;
	header('Content-Type: application/json');
	echo json_encode($result, JSON_PRETTY_PRINT);

}
?>