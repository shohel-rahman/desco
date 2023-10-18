   <?php
      ini_set('display_errors', 1);
      ini_set('display_startup_errors', 1);
      error_reporting(E_ALL);

      $postedData = $_POST;
      $keysOfPostData = array_keys($_POST);         
      $year = $postedData['year'];
      $month = $postedData['month'];

      $divisionIds = array();
      $divisionLoad = array();
      for($i=2; $i<count($keysOfPostData); $i++){
         array_push($divisionIds,substr($keysOfPostData[$i],4));
         array_push($divisionLoad,$postedData[$keysOfPostData[$i]]);
      }
      
      $insertionQuery = "INSERT INTO desco.MAXIMUM_LOAD_DEMAND (DIVISION_ID, YEAR, MONTH, MAX_LOAD) VALUES ";
      for($i=0; $i<count($divisionIds); $i++){         
         $insertionQuery .= "(". $divisionIds[$i] .",". $year .",". $month .",". $divisionLoad[$i];
         if($i == count($divisionIds)-1){
            $insertionQuery .= ")";
         }
         else $insertionQuery .= "),";     
      } 
      require('../../../../opt/config/connection.php');
      $connection_maria = $mysqli;				
      if ($connection_maria -> connect_errno) {      
        echo "Failed to connect MySQL Server: " . $connection_maria -> connect_error;
        exit();
      }
      //echo $insertionQuery;
      //die();
      $connection_maria -> query($insertionQuery);
      $connection_maria -> close();
      $showDemandEntryUrl = "http://local.desco.org.bd/mod/show_demand.php?year=".$year."&month=".$month;
      header( "Location: $showDemandEntryUrl" );
   ?>