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
      $divisionNameSql = "SELECT `DIVISION_ID`,`DIVISION_NAME` FROM desco.`SALES_DISTRIBUTION_DIVISION` WHERE `DIVISION_ID` != 9999 ORDER BY `DIVISION_NAME` ASC";
      $allDivisionName = $connection_maria -> query($divisionNameSql);    
      ?>

      <body>
        <div class='row w-80 mx-4 px-4 py-2 align-items-center'>
          <h1 class='text-center'>Maximum Load Demand(MW)</h1>          
        </div>
        <form action="save_load_data.php" method = 'post'>
            <div class="w-100 my-4 d-print-none">
              <div class="row w-60 mx-5 px-4 py-3 align-items-center">
                <div class="col-3"></div>  
                <div class="col-3 form-group">
                    <select class="form-select" aria-label="Select Year" name="year" id="yearSelector" required>
                        <option selected disabled value="">Select Year</option>
                        <option value="2023">2023</option>
                        <option value="2022">2022</option>
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
              <?php
                  $rowIndex = 0;                  
                  while($rowIndex < $allDivisionName -> num_rows){
                    $nameIdPair = $allDivisionName -> fetch_row();
                    $divisionRow =
                      "<div class='row w-80 mx-4 px-4 py-1 align-items-center form-group'>
                        <div class='col-2'>
                          <label class='form-label w-100 text-center' name='". $nameIdPair[1] ."' for='divId". $nameIdPair[0] ."'>". $nameIdPair[1] ."</label>
                        </div>
                        <div class='col-1'>
                          <input class='form-control' type='number' id='divId". $nameIdPair[0] ."' name='load". $nameIdPair[0] ."'min='0' step='1' required>
                        </div>";                  

                    $rowIndex++;                    
                    if(!$allDivisionName->data_seek($rowIndex)){ 
                      $divisionRow .= "</div>";                      
                      echo $divisionRow;                      
                      break;
                    }
                    $nameIdPair = $allDivisionName -> fetch_row();
                    $divisionRow .=
                      "<div class='col-2'>
                        <label class='form-label w-100 text-center' name='". $nameIdPair[1] ."' for='divId". $nameIdPair[0] ."'>". $nameIdPair[1] ."</label>
                      </div>
                      <div class='col-1'>
                        <input class='form-control' type='number' id='divId". $nameIdPair[0] ."' name='load". $nameIdPair[0] ."'min='0' step='1' required>
                      </div>";
                    
                    $rowIndex++;                    
                    if(!$allDivisionName->data_seek($rowIndex)){
                      $divisionRow .= "</div>";                      
                      echo $divisionRow;                    
                      break;
                    }                                    
                                       
                    $nameIdPair = $allDivisionName -> fetch_row();
                    $divisionRow .=
                      "<div class='col-2'>
                        <label class='form-label w-100 text-center' name='". $nameIdPair[1] ."' for='divId". $nameIdPair[0] ."'>". $nameIdPair[1] ."</label>
                      </div>
                      <div class='col-1'>
                        <input class='form-control' type='number' id='divId". $nameIdPair[0] ."' name='load". $nameIdPair[0] ."'min='0' step='1' required>
                      </div>";

                    $rowIndex++;                    
                    if(!$allDivisionName->data_seek($rowIndex)){
                       $divisionRow .= "</div>";                      
                      echo $divisionRow;                    
                      break;
                    }  

                    $nameIdPair = $allDivisionName -> fetch_row();
                    $divisionRow .=
                      "<div class='col-2'>
                        <label class='form-label w-100 text-center' name='". $nameIdPair[1] ."' for='divId". $nameIdPair[0] ."'>". $nameIdPair[1] ."</label>
                      </div>
                      <div class='col-1'>
                        <input class='form-control' type='number' id='divId". $nameIdPair[0] ."' name='load". $nameIdPair[0] ."'min='0' step='1' required>
                      </div>";
          
                    $divisionRow .= "</div>";                      
                    echo $divisionRow;
                    
                    $rowIndex++;
                    if(!$allDivisionName->data_seek($rowIndex))
                      break;                    
                  }                                                                                                                                              
                ?>
              <div class='form-group row mt-5 mx-4 px-4 align-items-center'>
                <div class='col-4'></div>
                <div class='col-2'><label class='form-label' name='DESCO' for='divId9999'>DESCO</label></div>
                <div class='col-2'><input class='form-control' type='number' step='1' id='divId9999' name='load9999'min='0' required onChange='' value=''></div>
                <div class='col-4'><input type='submit' value='Save Data'></input></div>
              </div>              
            </div>            
        </form>
      </body>
   <?php $connection_maria -> close();?>
</html>