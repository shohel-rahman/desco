<!DOCTYPE html>
<html>
   <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">    
    <title>Demand | DESCO</title>    
   </head>
   <?php
      ini_set('display_errors', 1);
      ini_set('display_startup_errors', 1);
      error_reporting(E_ALL);
      
      require('../../../../opt/config/connection.php');
      $connection_maria = $mysqli;				
      if ($connection_maria -> connect_errno) {      
        echo "Failed to connect MySQL Server: " . $connection_maria -> connect_error;
        exit();
      }

      if(!empty($_GET['year'])){
         $year = $_GET['year'];
         $month = $_GET['month'];
      }
      else{
         $yearSql = "SELECT MAX(YEAR) as 'year' FROM desco.`MAXIMUM_LOAD_DEMAND`";   
         $resultYear = $connection_maria -> query($yearSql);
         $year = $resultYear ->  fetch_assoc()['year'];
   
         $monthSql = "SELECT MAX(MONTH) as 'month' FROM desco.`MAXIMUM_LOAD_DEMAND` WHERE `year` = $year";
         $resultMonth = $connection_maria -> query($monthSql);   
         $month = $resultMonth -> fetch_assoc()['month'];
      }

      //var_dump($_POST);
      $PostedData = $_POST;
      $keysOfPostData = array_keys($_POST);   

      $maxLoadSql = "SELECT a.division_name, b.max_load FROM desco.`MAXIMUM_LOAD_DEMAND` b JOIN desco.SALES_DISTRIBUTION_DIVISION a ON a.DIVISION_ID = b.DIVISION_ID 
                     WHERE YEAR = $year AND MONTH = $month AND a.DIVISION_ID !=9999 ORDER BY a.DIVISION_NAME ASC";
      $allDivisionLoad = $connection_maria -> query($maxLoadSql);
      ?>

   <body>
      <form action="show_demand.php" method = 'GET'>
         <fieldset class="well the-fieldset">
            <div class="w-100 my-4 d-print-none">
               <div class="row w-100 mx-4 px-4">
                  <div class="col">
                     <select class="form-select" aria-label="Select Year" name="year" id="yearSelector" required>
                        <option selected disabled value="">Select Year</option>
                        <option value="2023">2023</option>
                        <option value="2022">2022</option>
                        <option value="2021">2021</option>                      
                     </select>
                  </div>
                  <div class="col">
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
                  <div class="col">
                     <button type="submit" class="btn btn-primary">Show Load Demand</button>
                  </div>
               </div>
            </div>
         </fieldset>
      </form>
      <div class="table-responsive mt-4 mx-3">
         <table class="table table-bordered border-secondary text-center align-middle">
            <thead>
               <tr>                  
                  <th scope="col-3">Division Name</th>
                  <th scope="col-1">Maximum Load Demand (MW)</th>                                                                  
               </tr>
            </thead>
            <tbody>      
               <?php
                  $printRow ="";
                  foreach($allDivisionLoad as $dataRow){
                     $printRow .= 
                        "<tr>
                           <td scope='col-3'>" . $dataRow['division_name'] . "</td>
                           <td scope='col-3'>" . $dataRow['max_load'] . "</td>                                 
                        </tr>";            
                  }
                  $printRow .="<tr>               
                        <th scope='col-3'>DESCO</th>
                        <th scope='col-3'>" . getLoadById($year,$month,$connection_maria) . "</th>                        
                     </tr>";  
                  echo $printRow;     
               ?>
            </tbody> 
         </table>     
      </div>
   </body>
   <?php 
      function getLoadById($year,$month,$connection){
         $sql="SELECT max_load FROM desco.`MAXIMUM_LOAD_DEMAND` WHERE YEAR = $year AND MONTH = $month AND DIVISION_ID = 9999";      
         $resultSet = $connection -> query($sql)-> fetch_array(MYSQLI_ASSOC);
         return $resultSet ['max_load'];
      }
      $connection_maria -> close();
   ?>
</html>