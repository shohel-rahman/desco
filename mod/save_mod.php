   <?php          
      ini_set('display_startup_errors', 1);
      error_reporting(E_ALL & ~E_NOTICE);
      ini_set('display_errors', 1);
      ini_set('error_reporting', E_ALL);

      // var_dump($_POST);   
      $postedData = $_POST;
      $keysOfPostData = array_keys($_POST);   
      $year = $postedData['year'];
      $month = $postedData['month'];
      
      require('../../../../opt/config/orcl_con.php');
      $actualMod = rawModPull($year,$month,$conn);

      //$sddNos = $actualMod -> recordCount();            
      
      require('../../../../opt/config/connection.php');
      $connection_maria = $mysqli;
      if ($connection_maria -> connect_errno) {      
        echo "Failed to connect MySQL Server: " . $connection_maria -> connect_error;
        exit();        
      }
      //For Published MOD
      $insertionQueryPublished = "INSERT INTO desco.PUBLISHED_MOD (`division_id`, `year`, `month`, `financial_year`, `consumer`, `import_unit`, `sales_unit`, `system_loss`, 
                                 `bill_amount`, `sales_rate`, `collection_amount`, `bc_ratio`, `ci_ratio`, `ytd_import_unit`, `ytd_sales_unit`, `ytd_system_loss`, 
                                 `ytd_bill_amount`, `YTD_SALES_RATE`, `ytd_coll_amount`, `ytd_bc_ratio`, `ytd_ci_ratio`, `acc_receivable_amnt`, `acc_receivable_eqv_month`,
                                 `BILLED_VAT`, `COLLECTED_VAT`) VALUES ";   
      
      $financialYear = getFinYear($year,$month);      
      
      foreach($actualMod as $dataRow){
         $divisionId = findDivIdByName($dataRow['DIVISION'],$connection_maria); 
         
         $divMonthlySales = $dataRow['SALES_UNIT'] + (double)$postedData['slsadjst'.$divisionId];
         $divMonthlyCollection = $dataRow['COLLECTION_AMOUNT'] + (double)$postedData['coladj'.$divisionId];
         $divMonthlyBC = ($divMonthlyCollection / $dataRow['BILL_AMOUNT'])*100;
         $divMonthlyCI = ($divMonthlySales*$divMonthlyBC)/(double)$postedData['import'.$divisionId];

         $divYtdBill =  (double)$postedData['prvmnthytdbill'.$divisionId] + $dataRow['BILL_AMOUNT'];
         $divYtdCollection = (double)$postedData['prvmnthytdcoll'.$divisionId] + $divMonthlyCollection;
         $divYtdBC = ($divYtdCollection/$divYtdBill)*100;
         $divYtdCI = ((double)$postedData['ytdsales'.$divisionId]*$divYtdBC)/(double)$postedData['ytdimport'.$divisionId];
         //var_dump($dataRow);         
         $insertionQueryPublished .="(".
                              $divisionId.",".
                              $dataRow['YEAR'].",".
                              $dataRow['MONTH'].",".
                              "'$financialYear'".",".
                              $dataRow['TOTAL_CONSUMER'].",".
                              ((double)$postedData['import'.$divisionId]).",".
                              $divMonthlySales.",".
                              calculateSystemLoss((double)$postedData['import'.$divisionId],  $divMonthlySales).",".
                              $dataRow['BILL_AMOUNT'].",".
                              ($dataRow['BILL_AMOUNT'] / $divMonthlySales).",".                                                                           
                              $divMonthlyCollection.",".                             
                              $divMonthlyBC.",".
                              $divMonthlyCI.",".                                                            
                              ((double)$postedData['ytdimport'.$divisionId]).",".
                              ((double)$postedData['ytdsales'.$divisionId]).",".
                              calculateSystemLoss((double)$postedData['ytdimport'.$divisionId], (double)$postedData['ytdsales'.$divisionId]).",".                              
                              $divYtdBill.",".
                              ($divYtdBill / (double)$postedData['ytdsales'.$divisionId]).",".                              
                              $divYtdCollection.",".                              
                              $divYtdBC.",".
                              $divYtdCI.",".                              
                              $dataRow['PRESENT_RECEIVABLE'].",".
                              $dataRow['PRESENT_EQUIVALENT'].",".                                                        
                              $dataRow['BILLED_VAT'].",".
                              $dataRow['COLLECTED_VAT']."),";         
      }
      
      //For Actual MOD      
      $insertionQueryActual = "INSERT INTO desco.ACTUAL_MOD (`division_id`, `year`, `month`, `consumer`, `import_unit`, `audited_import_unit`, `sales_unit`, `sales_adjustment`, 
                           `system_loss`, `bill_amount`, `sales_rate`, `collection_amount`, `bc_ratio`, `ci_ratio`, `ytd_import_unit`, `ytd_audited_import_unit`, 
                           `ytd_sales_unit`, `ytd_sales_adjustment`, `ytd_system_loss`, `ytd_bill_amount`, `YTD_SALES_RATE`, `ytd_coll_amount`, `ytd_bc_ratio`, 
                           `ytd_ci_ratio`, `acc_receivable_amnt`, `acc_receivable_eqv_month`, `BILLED_VAT`, `COLLECTED_VAT`, collection_adjustment) VALUES ";
      foreach($actualMod as $dataRow){
         $divisionId = findDivIdByName($dataRow['DIVISION'],$connection_maria);
         $insertionQueryActual .= "(". 
                              $divisionId.",". 
                              $dataRow['YEAR'].",".
                              $dataRow['MONTH'].",".
                              $dataRow['TOTAL_CONSUMER'].",".
                              $dataRow['IMPORT_UNIT'].",".
                              ((double)$postedData['import'.$divisionId]).",".
                              $dataRow['SALES_UNIT'].",".
                              ((double)$postedData['slsadjst'.$divisionId]).",".
                              $dataRow['SYSTEM_LOSS'].",".
                              $dataRow['BILL_AMOUNT'].",".
                              ($dataRow['BILL_AMOUNT'] / $dataRow['SALES_UNIT']).",".
                              $dataRow['COLLECTION_AMOUNT'].",".
                              $dataRow['BC_RATIO'].",".
                              $dataRow['CI_RATIO'].",".
                              $dataRow['IMPORT_YEAR_TO_DATE'].",".
                              ((double)$postedData['ytdimport'.$divisionId]).",".
                              $dataRow['SALES_YEAR_TO_DATE'].",".
                              ((double)$postedData['prvmntytdslsadjst'.$divisionId] + (double)$postedData['slsadjst'.$divisionId]).",".
                              $dataRow['SL_YTD'].",".
                              $dataRow['B_AMOUNT_YEAR_TO_DATE'].",".
                              $dataRow['B_AMOUNT_YEAR_TO_DATE'] / $dataRow['SALES_YEAR_TO_DATE'].",".
                              $dataRow['C_AMOUNT_YEAR_TO_DATE'].",".
                              $dataRow['BC_RATIO_YTD'].",".
                              $dataRow['CI_RATIO_YTD'].",".
                              $dataRow['PRESENT_RECEIVABLE'].",".
                              $dataRow['PRESENT_EQUIVALENT'].",".                                                       
                              $dataRow['BILLED_VAT'].",".
                              $dataRow['COLLECTED_VAT'].",".
                              (double)$postedData['coladj'.$divisionId]."),";
      }   
      
      $divisionId = "9999";
      $descoMod = rawModPullDESCO($year,$month,$conn);


      //For DESCO Published
      $desSysLoss = calculateSystemLoss((double)$postedData['import'.$divisionId], $descoMod['SALES_UNIT'] + (double)$postedData['slsadjst'.$divisionId]);
      $desMonthlyCollection = $descoMod['COLLECTION_AMOUNT'] + (double)$postedData['coladj'.$divisionId];
      $desBcRatio = ($desMonthlyCollection / (double)$descoMod['BILL_AMOUNT'])*100;
      $desCiRatio = $desBcRatio * (1 - ($desSysLoss/100));

      $desYtdSysLoss = calculateSystemLoss((double)$postedData['ytdimport'.$divisionId], (double)$postedData['ytdsales'.$divisionId]);
      $desYtdBill =  (double)$postedData['prvmnthytdbill'.$divisionId] + $descoMod['BILL_AMOUNT'];
      $desYtdCollection = (double)$postedData['prvmnthytdcoll'.$divisionId] + $desMonthlyCollection;
      $desYtdBcRatio = ( $desYtdCollection/$desYtdBill )*100;
      $desYtdCiRatio = $desYtdBcRatio * (1 - ($desYtdSysLoss/100));  

      $insertionQueryPublished .= "(".
                                 $divisionId.",".
                                 $year.",".
                                 $month.",".
                                 "'$financialYear'".",".
                                 $descoMod['TOTAL_CONSUMER'].",".
                                 ((double)$postedData['import'.$divisionId]).",".
                                 ($descoMod['SALES_UNIT'] + (double)$postedData['slsadjst'.$divisionId]).",".
                                 $desSysLoss.",".
                                 $descoMod['BILL_AMOUNT'].",".
                                 ($descoMod['BILL_AMOUNT'] / ($descoMod['SALES_UNIT'] + (double)$postedData['slsadjst'.$divisionId])).",".                                                                           
                                 $desMonthlyCollection.",".                             
                                 $desBcRatio.",".
                                 $desCiRatio.",".                                                            
                                 ((double)$postedData['ytdimport'.$divisionId]).",".
                                 ((double)$postedData['ytdsales'.$divisionId]).",".
                                 $desYtdSysLoss.",".                              
                                 $desYtdBill.",".
                                 ($desYtdBill / (double)$postedData['ytdsales'.$divisionId]).",".                              
                                 $desYtdCollection.",".                              
                                 $desYtdBcRatio.",".
                                 $desYtdCiRatio.",".                              
                                 $descoMod['PRESENT_RECEIVABLE'].",". 
                                 $descoMod['PRESENT_EQUIVALENT'].",".                                                           
                                 $descoMod['BILLED_VAT'].",".
                                 $descoMod['COLLECTED_VAT'].")";                                                        

      //For DESCO Actual

      $desSysLoss = calculateSystemLoss((double)$descoMod['IMPORT_UNIT'],(double)$descoMod['SALES_UNIT']);
      $desBcRatio = ($descoMod['COLLECTION_AMOUNT']/$descoMod['BILL_AMOUNT'])*100;
      $desCiRatio = $desBcRatio * (1 - ($desSysLoss/100));
      $desYtdSysLoss = calculateSystemLoss((double)$descoMod['IMPORT_YEAR_TO_DATE'],(double)$descoMod['SALES_YEAR_TO_DATE']);  
      $desYtdBcRatio = ($descoMod['C_AMOUNT_YEAR_TO_DATE']/$descoMod['B_AMOUNT_YEAR_TO_DATE'])*100;    
      $desYtdCiRatio = $desYtdBcRatio * (1 - ($desYtdSysLoss/100));    

      $insertionQueryActual .= "(". 
                              $divisionId.",". 
                              $year.",".
                              $month.",".
                              $descoMod['TOTAL_CONSUMER'].",".
                              $descoMod['IMPORT_UNIT'].",".
                              ((double)$postedData['import'.$divisionId]).",".
                              $descoMod['SALES_UNIT'].",".
                              ((double)$postedData['slsadjst'.$divisionId]).",".
                              $desSysLoss.",".
                              $descoMod['BILL_AMOUNT'].",".
                              ($descoMod['BILL_AMOUNT'] / $descoMod['SALES_UNIT']).",".
                              $descoMod['COLLECTION_AMOUNT'].",".
                              $desBcRatio.",".
                              $desCiRatio.",".
                              $descoMod['IMPORT_YEAR_TO_DATE'].",".
                              ((double)$postedData['ytdimport'.$divisionId]).",".
                              $descoMod['SALES_YEAR_TO_DATE'].",".                              
                              ((double)$postedData['prvmntytdslsadjst'.$divisionId] + (double)$postedData['slsadjst'.$divisionId]).",".
                              $desYtdSysLoss.",".
                              $descoMod['B_AMOUNT_YEAR_TO_DATE'].",".
                              $descoMod['B_AMOUNT_YEAR_TO_DATE'] / $descoMod['SALES_YEAR_TO_DATE'].",".
                              $descoMod['C_AMOUNT_YEAR_TO_DATE'].",".
                              $desYtdBcRatio.",".
                              $desYtdCiRatio.",".
                              $descoMod['PRESENT_RECEIVABLE'].",". 
                              $descoMod['PRESENT_EQUIVALENT'].",".                                                            
                              $descoMod['BILLED_VAT'].",".
                              $descoMod['COLLECTED_VAT'].",".
                              (double)$postedData['coladj'.$divisionId].")";
 

      
      //Insertion into Published MOD Table
      $connection_maria -> query($insertionQueryPublished);      
      if($connection_maria->affected_rows>0) echo $connection_maria->affected_rows." Rows Inserted into Published_MOD Table<br/><br/>";
      else echo "Data for this month is already present in Published_MOD table<br/><br/>";

     
      //Insertion into Actual MOD Table
      $connection_maria -> query($insertionQueryActual);
      if($connection_maria->affected_rows>0) echo $connection_maria->affected_rows." Rows Inserted into Actual_MOD Table<br/><br/>";
      else echo "Data for this month is already present in Actual_MOD table <br/><br/>";

      //Updating YTD Collection Adjustment in Actual MOD table
      echo "YTD collection Adjustment updated for ".updateYtdCollAdjustment($year, $month, $connection_maria)." Divisions <br />";

      //Inserting Prepaid Data into Mysql server
      echo "<br />";
      include_once('prepaid_mod_puller.php');      
      echo insertPrepaidMOD($year, $month, $conn, $connection_maria);


      // Inserting Actual Tariffwise data into Mysql Server
      echo "<br />";
      include_once('tariff_mod_puller.php');      
      echo insertActualTariffMOD($year, $month, $conn, $connection_maria);


      //Distribute tariffwise Adjustment and insert into Published Tariffwise MOD Table
      include_once('distribute_tariff_adjustment.php');
      adjustNInsert($year, $month, $connection_maria);

   
      //Maximam Demand Row Creation if Needed
      echo "<br /><br />";
      if(getMaxDemandRowCount($year,$month,$connection_maria)<=0){
         if(createDummyMaxDemandRows($year,$month,$connection_maria))
            echo "Successfully inserted dummy rows into MAXIMUM_LOAD_DEMAND table.<br/><br/>";
         else echo "Could not insert dummy rows into MAXIMUM_LOAD_DEMAND table.<br/><br/>";
      }else{
         echo "Maximum Load Demand Data of ".$month."-".$year." is already available. No need to insert dummy rows.<br/><br/>";
      }

      $collectionAdjusted = isCollectionAdjusted($year,$month,$connection_maria);
      if($collectionAdjusted){
         echo "There is a collection adjustment of amount ".$collectionAdjusted."<br/><br/>
            Please upload the Division-wise, Tariff-wise Collection Adjustment<br/><br/>";

            echo "Please maintain the format given below(mandatory):
               <table>
                  <tr><th>division_id</th><th>division_name</th><th>tariff</th><th>collection_adjustment</th><th>year</th><th>month</th></tr>
                  <tr><td>19</td><td>Badda</td><td>A</td><td>148687</td><td>2023</td><td>12</td></tr>
                  <tr><td>19</td><td>Badda</td><td>E</td><td>89524.25</td><td>2023</td><td>12</td></tr>
                  <tr><td>19</td><td>Badda</td><td>MT1</td><td>-5482.98</td><td>2023</td><td>12</td></tr>
                  <tr><td>...</td><td>..........</td><td>...</td><td>......</td><td>2023</td><td>12</td></tr>
                  <tr><td>...</td><td>..........</td><td>...</td><td>......</td><td>2023</td><td>12</td></tr>
                  <tr><td>9999</td><td>DESCO</td><td>A</td><td>2235492.58</td><td>2023</td><td>12</td></tr>
                  <tr><td>9999</td><td>DESCO</td><td>C1</td><td>65492.58</td><td>2023</td><td>12</td></tr>
                  <tr><td>9999</td><td>DESCO</td><td>MT1</td><td>2365492.58</td><td>2023</td><td>12</td></tr>
                  <tr><td>9999</td><td>DESCO</td><td>E</td><td>2365492.58</td><td>2023</td><td>12</td></tr>
               </table>
            <br/><br/>";

         echo "<form action='distribute_trf_coll_adj.php' method='post' enctype='multipart/form-data'>
                  <input type='hidden' value='$year' name='year'>
                  <input type='hidden' value='$month' name='month'>
                  Select Excel file to upload:
                  <input type='file' name='fileToUpload' id='fileToUpload'>
                  <input type='submit' value='Upload' name='submit'>
               </form>";
         echo"<h3 style='color:red;'>Please upload Microsoft Excel file(.xls or .xlsx) only size wihtin 1 MB.</h3>";
      }
      else{
         echo "<br/><br/><button onclick=location.href='http://local.desco.org.bd/mod/mod_report_format.php' type='button'>Print MOD Report</button>";
      }

      

      //Close DB
      $connection_maria -> close();
      oci_close($conn);	

      function isCollectionAdjusted($year,$month,$connection){
         $sql = "SELECT collection_adjustment FROM desco.`ACTUAL_MOD` WHERE year = $year AND month=$month AND division_id=9999 AND collection_adjustment<>0";
         $resultSet= $connection -> query($sql);
         return $resultSet->fetch_array(MYSQLI_ASSOC)['collection_adjustment'];
      }

      function updateYtdCollAdjustment($year, $month, $connection){
         if($month == 7)
            $sql = "UPDATE desco.ACTUAL_MOD SET ytd_coll_adjustment = collection_adjustment WHERE year = $year AND month = $month";
         else if($month == 1)
            $sql = "UPDATE desco.ACTUAL_MOD a JOIN desco.ACTUAL_MOD b SET a.ytd_coll_adjustment = a.collection_adjustment + IFNULL(b.ytd_coll_adjustment,0)
             WHERE a.division_id = b.division_id AND a.year =$year AND a.month =$month AND b.year = $year-1 AND b.month =12";
         else
            $sql = "UPDATE desco.ACTUAL_MOD a JOIN desco.ACTUAL_MOD b SET a.ytd_coll_adjustment = a.collection_adjustment + IFNULL(b.ytd_coll_adjustment,0)
             WHERE a.division_id = b.division_id AND a.year =$year AND a.month =$month AND b.year = $year AND b.month =$month-1";
         $connection -> query($sql);
         return $connection->affected_rows; 
      }

      function getMaxDemandRowCount($year, $month, $connection){
         $sql = "SELECT count(*) AS Total_Rows FROM desco.`MAXIMUM_LOAD_DEMAND` WHERE YEAR = $year AND MONTH = $month";
         $resultSet = $connection -> query($sql);
         return $resultSet->fetch_array(MYSQLI_ASSOC)['Total_Rows'];
      }

      function createDummyMaxDemandRows($year, $month, $connection){
         $sql = "INSERT INTO desco.`MAXIMUM_LOAD_DEMAND`(`DIVISION_ID`, `YEAR`, `MONTH`, `MAX_LOAD`) VALUES ";
         $allDivision = getAllDivision($connection);
         foreach($allDivision as $division){
            $sql .= "(".
                        $division['DIVISION_ID'].",".
                        $year.",".
                        $month.",".
                        "0
                     ),";
         }
         $sql = substr($sql,0,strlen($sql)-1);        
         $connection -> query($sql);
      }
      
      function getAllDivision($connection){
         $sql = "SELECT DIVISION_ID,DIVISION_NAME FROM desco.`SALES_DISTRIBUTION_DIVISION`";
         $resultSet = $connection -> query($sql);
         return $resultSet;
      }
      
      function rawModPullDESCO($year,$month,$connection){
         $sql = "SELECT SUM(MST.TOTAL_CONSUMER) TOTAL_CONSUMER, SUM(MST.IMPORT_UNIT) IMPORT_UNIT, SUM(MST.SALES_UNIT) SALES_UNIT, SUM(MST.BILL_AMOUNT) BILL_AMOUNT, 
         SUM(MST.COLLECTION_AMOUNT) COLLECTION_AMOUNT, SUM(MST.IMPORT_YEAR_TO_DATE) IMPORT_YEAR_TO_DATE, SUM(MST.SALES_YEAR_TO_DATE) SALES_YEAR_TO_DATE,
         SUM(MST.B_AMOUNT_YEAR_TO_DATE) B_AMOUNT_YEAR_TO_DATE, SUM(MST.C_AMOUNT_YEAR_TO_DATE) C_AMOUNT_YEAR_TO_DATE, SUM(NVL(MST.BILLED_VAT,0)) BILLED_VAT,
         SUM(NVL(MST.COLLECTED_VAT,0)) COLLECTED_VAT, SUM(CV.PRESENT_RECEIVABLE) PRESENT_RECEIVABLE, SUM(CV.PRESENT_RECEIVABLE) / SUM(CV.AVG_BILL) PRESENT_EQUIVALENT  
         FROM DESCO.MOD_SUMMARY_TOTAL MST JOIN DESCO.CATEGORYWISE_COORD_WITH_VAT CV ON CV.YEAR = MST.YEAR AND CV.MONTH = MST.MONTH AND CV.DIVISION = MST.DIVISION
         Where MST.YEAR = $year AND MST.MONTH = $month AND CV.CATEGORY = 'Total'";       
                      
         $parsed = oci_parse($connection,$sql);
         oci_execute($parsed);
         $resultSet = oci_fetch_array($parsed, OCI_BOTH + OCI_RETURN_NULLS);
         oci_free_statement($parsed);		 
         return $resultSet; 
      }
      
      function rawModPull($year,$month,$connection){  
         $sql = "SELECT MST.DIVISION, MST.YEAR, MST.MONTH, MST.TOTAL_CONSUMER, MST.IMPORT_UNIT, MST.SALES_UNIT, MST.SYSTEM_LOSS, MST.BILL_AMOUNT, MST.COLLECTION_AMOUNT, 
               MST.BC_RATIO, MST.CI_RATIO, MST.IMPORT_YEAR_TO_DATE, MST.SALES_YEAR_TO_DATE, MST.SL_YTD, MST.B_AMOUNT_YEAR_TO_DATE, MST.C_AMOUNT_YEAR_TO_DATE, 
               MST.BC_RATIO_YTD, MST.CI_RATIO_YTD, NVL(MST.BILLED_VAT,0) AS BILLED_VAT, NVL(MST.COLLECTED_VAT,0) AS COLLECTED_VAT, 
               NVL(CV.PRESENT_RECEIVABLE,0) PRESENT_RECEIVABLE, NVL(CV.PRESENT_EQUIVALENT,0) PRESENT_EQUIVALENT
               FROM DESCO.MOD_SUMMARY_TOTAL MST JOIN DESCO.CATEGORYWISE_COORD_WITH_VAT CV ON CV.DIVISION = MST.DIVISION AND CV.YEAR = MST.YEAR AND CV.MONTH = MST.MONTH 
               Where MST.YEAR = $year AND MST.MONTH = $month AND CV.CATEGORY = 'Total'
               ORDER BY MST.DIVISION ASC";            
               
         $parsed = oci_parse($connection, $sql);
         oci_execute($parsed);
         oci_fetch_all($parsed, $resultSet, 0, -1, OCI_FETCHSTATEMENT_BY_ROW	+ OCI_ASSOC);
         oci_free_statement($parsed);
         return $resultSet;
      }
     
      
     function findDivIdByName($divisionName, $connection){
         $sql = "SELECT DIVISION_ID FROM desco.SALES_DISTRIBUTION_DIVISION WHERE DIVISION_NAME = '$divisionName'";
         $result = $connection -> query($sql) -> fetch_array(MYSQLI_ASSOC); 
         $divisionId = $result['DIVISION_ID'];
         return $divisionId;
      }
      function getFinYear($year,$month){
         if ($month >= 7) return (string)$year ."-". (string)($year+1);
         else return (string)($year-1) ."-". (string)$year;
      }
      function calculateSystemLoss($import,$sales){
         return (($import-$sales)/$import)*100;
      }	
   ?>