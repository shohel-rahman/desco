<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
      <title>MOD | DESCO</title>
   </head>
   <?php 
      require('../../../../opt/config/connection.php');
      $connection_maria = $mysqli;
      if ($connection_maria -> connect_errno) {      
        echo "Failed to connect MySQL Server: " . $connection_maria -> connect_error;
        exit();
      }
      // var_dump($connection_maria);
      $divisionNameList = findAllDivisionName($connection_maria);      
      require('../../../../opt/config/orcl_con.php');
      // $_SESSION['user'] = ""; 
      // var_dump($conn);    
    ?>
   <body>
      <div class='row w-80 mx-4 px-4 py-2 align-items-center'>
         <h1 class='text-center'>Monthly Operational Data</h1>
      </div>
      <form action="mod_dashboard.php" method = 'GET'>
        <div class="w-100 my-4 d-print-none">
          <div class="row w-60 mx-5 px-4 py-3 align-items-center">
              <div class="col-3"></div>
              <div class="col-3 form-group">
                <select class="form-select" aria-label="Select Year" name="year" id="yearSelector" required>
                    <option selected disabled value="">Select Year</option>
                    <option value="2023">2023</option>                    
                </select>
              </div>
              <div class="col-3 form-group">
                <select class="form-select" aria-label="Select Month" name="month" id="monthSelector" required>
                    <option selected disabled value="">Select Month</option>
                    <option value="1">January</option>
                    <option value="2">February</option>
                    <option value="3">March</option>
                    <option value="4">April</option>
                    <option value="5">May</option>
                    <option value="6">June</option>
                    <option value="7">July</option>
                    <option value="8">August</option>
                    <option value="9">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>
              </div>
              <div class="col-3"></div>
          </div>
          <div class="row w-90 mx-5 px-4 py-3 align-items-center">                          
            <div class="col-3">              
              <button class="btn btn-outline-success" id= "confirm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseConfirm" aria-expanded="false" 
                aria-controls="collapseConfirm" name="pull">Pull MOD to HMIS from Division 
              </button>               
            </div>
            <div class="col-3">              
              <button class="btn btn-outline-danger" id= "cancel" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCancel" aria-expanded="false" 
                aria-controls="collapseCancel" name="cancel"> Cancel MOD of Division
              </button>               
            </div>
            <div class="col-1">
              <input type="submit" class="btn btn-outline-danger" name ="submit" value='Go' id='submit'>              
            </div>
            <div class="col-1">              
              <button class="btn btn-primary" onclick="location='http://local.desco.org.bd/mod/mod_dashboard.php';">Reset</button>
            </div>
          </div>
          <div class="row w-95 mx-5 px-4 py-3 align-items-center">
            <div class="collapse" id="collapseConfirm">
                <div class='form-check form-check-inline'>
                  <input class='form-check-input' type='checkbox' name='confirmId9999' id='confirmId9999' value='All'>
                  <label class='form-check-label' for='confirmId9999'>All</label>
                </div>
                <?php 
                  foreach($divisionNameList as $division){
                    echo "<div class='form-check form-check-inline'>
                            <input class='form-check-input' type='checkbox' name='confirmId".$division['DIVISION_ID']."' id='confirmId".$division['DIVISION_ID']."' value='".$division['DIVISION_NAME']."'>
                            <label class='form-check-label' for='confirmId".$division['DIVISION_ID']."'>".$division['DIVISION_NAME']."</label>
                          </div>";
                  }
                ?>
              </div>

            <div class="collapse" id="collapseCancel">
              <div class='form-check form-check-inline'>
                <input class='form-check-input' type='checkbox' name='cancelId9999' id='cancelId9999' value='All'>
                <label class='form-check-label' for='cancelId9999'>All</label>
              </div>
              <?php 
                foreach($divisionNameList as $division){
                  echo "<div class='form-check form-check-inline'>
                          <input class='form-check-input' type='checkbox' name='cancelId".$division['DIVISION_ID']."' id='cancelId".$division['DIVISION_ID']."' value='".$division['DIVISION_NAME']."'>
                          <label class='form-check-label' for='cancelId".$division['DIVISION_ID']."'>".$division['DIVISION_NAME']."</label>
                        </div>";
                }
              ?>
            </div>
          </div>           
        </div>
      </form>
      <?php 
        if($_GET['submit'] == 'Go'){          
          $year = $_GET['year'];          
          $month = $_GET['month'];          
          $keysOfPostData = array_keys($_GET);
          $confirmList = array();
          $cancelList = array();
          for($i = 0; $i<sizeof($keysOfPostData); $i++){
            if(substr($keysOfPostData[$i],0,9) == "confirmId"){
              array_push($confirmList,$_GET[$keysOfPostData[$i]]);
            }
            else if(substr($keysOfPostData[$i],0,8) == "cancelId"){
              array_push($cancelList,$_GET[$keysOfPostData[$i]]);
            }
          }
          // print_r($cancelList);  
          if(count($confirmList) != 0){                                                       
            if($confirmList[0] == 'All'){  
              unset($confirmList[0]);    
              foreach($divisionNameList as $division){                       
                array_push($confirmList,$division['DIVISION_NAME']);
              }          

              if(checkConnectivity($conn) > 0){ 
                $sql = "SELECT DIVISION FROM DESCO.MOD_SUMMARY_TOTAL WHERE MONTH = $month AND YEAR = $year ORDER BY DIVISION ASC"; 
                setPullList($conn, $sql, $confirmList, $year, $month);                                         
              }else{
                echo "Could not connect to HMIS. Please ensure connectivity then try again.<br/>";
              }                
            }
            else{
              if(checkConnectivity($conn) > 0){
                $sql = "SELECT UPPER(DIVISION) AS DIVISION FROM DESCO.MOD_SUMMARY_TOTAL WHERE MONTH = $month AND YEAR = $year AND UPPER(DIVISION) IN(";
                foreach($confirmList as $division){
                  $sql .= "'$division'".",";
                }
                $sql = substr($sql,0,strlen($sql)-1);
                $sql .= ") ORDER BY DIVISION ASC";
                setPullList($conn, $sql, $confirmList, $year, $month);                          
              }else{
                echo "Could not connect to HMIS. Please ensure connectivity.<br/>";
              }            
            }
          }

          //Cancel MOD
          if(count($cancelList) !=0){            
            if($cancelList[0] == 'All'){
              unset($cancelList[0]);                
              if(checkConnectivity($conn) > 0){ 
                $sql = "SELECT UPPER(DIVISION) AS DIVISION FROM DESCO.MOD_SUMMARY_TOTAL WHERE MONTH = $month AND YEAR = $year ORDER BY DIVISION ASC"; 
                setCancelList($conn, $sql, $cancelList, $year, $month);                                         
              }else{
                echo "Could not connect to HMIS. Please ensure connectivity then try again.<br/>";
              }            
            }
            else{
              if(checkConnectivity($conn) > 0){
                $sql = "SELECT UPPER(DIVISION) AS DIVISION FROM DESCO.MOD_SUMMARY_TOTAL WHERE MONTH = $month AND YEAR = $year AND UPPER(DIVISION) IN(";
                foreach($cancelList as $division){
                  $sql .= "'$division'".",";
                }              
                $sql = substr($sql,0,strlen($sql)-1);
                $sql .= ") ORDER BY DIVISION ASC";
                setCancelList($conn, $sql, $cancelList, $year, $month);                          
              }else{
                echo "Could not connect to HMIS. Please ensure connectivity.<br/>";
              }
            }
          }

        }                 
        oci_close($conn);	
      ?>
    </body>
    
    <?php
      function setCancelList($oracle_connection, $sql, $cancelList, $year, $month){
        $resultSet = $oracle_connection -> Execute($sql);        
        if($resultSet->recordCount() >0){
          echo "MOD of the follwing Division(s) will be canceled<br/><br/>";
          foreach($resultSet as $division){
            echo $division['DIVISION']."&emsp;";
            $key = array_search(strtoupper($division['DIVISION']), $cancelList,true);
            if($cancelList[$key] != $division['DIVISION']) 
              array_push($cancelList, $division['DIVISION']);
          }
        }
        $removeList = http_build_query($cancelList);
        $link = "http://local.desco.org.bd/mod/mod_cancel.php?year=$year&month=$month&$removeList";
        $btnScrpt = '<button class="btn btn-danger" onclick="location=';
        $btnScrpt .= "'$link'";
        $btnScrpt .= '">Cancel MOD</button>';
        echo $btnScrpt;      
      }

      function setPullList($oracle_connection, $sql, $confirmList, $year, $month){
        $parsed = oci_parse($oracle_connection, $sql);
        oci_execute($parsed);  
        oci_fetch_all($parsed, $result, 0, -1, OCI_FETCHSTATEMENT_BY_ROW	+ OCI_ASSOC);         
        $selected_rows = oci_num_rows($parsed);        
            
        if($selected_rows >0){
          echo "MOD of the follwing Division(s) are already present in HMIS<br/><br/>";
          foreach($result as $division){
            echo $division['DIVISION']."&emsp;";                
            $key = array_search(strtoupper($division['DIVISION']), $confirmList);
            unset($confirmList[$key]);
          }
          echo "<br/><br/>If you want to change an existing MOD first cancel that MOD then Pull again.<br/><br/>";
        }
        
        echo "<br/>MOD from the following divisions will be pulled now  <br/><br/>";              
        $confirmList = array_values($confirmList);            
        foreach($confirmList as $division){
          echo $division."&emsp;";
        }         
        oci_free_statement($parsed);
        
        
        $pullList = http_build_query($confirmList);
        $link = "http://local.desco.org.bd/mod/mod_pull.php?year=$year&month=$month&$pullList";
        $btnScrpt = '<button class="btn btn-primary" onclick="location=';
        $btnScrpt .= "'$link'";
        $btnScrpt .= '">Pull MOD</button>';
        echo $btnScrpt;
      }
      
      function findAllDivisionName($connection){
        $sql = "SELECT DIVISION_ID, DIVISION_NAME FROM desco.SALES_DISTRIBUTION_DIVISION WHERE DIVISION_ID != 9999 ORDER BY DIVISION_NAME ASC";
        $resultSet = $connection -> query($sql);
				return $resultSet;
      }

      function checkConnectivity($oracle_connection){          
        $sql ="SELECT DISTINCT(DIVISION) FROM DESCO.MOD_SUMMARY_TOTAL";                      
        $parsed = oci_parse($oracle_connection, $sql);
        oci_execute($parsed);
        $resultSet = oci_fetch_array($parsed, OCI_BOTH + OCI_RETURN_NULLS);    
        $selected_rows = oci_num_rows($parsed);
        oci_free_statement($parsed);
        return $selected_rows;
      }
    ?>
</html>