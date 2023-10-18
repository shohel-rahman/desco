<?php
   if(!empty($_GET['year'])){
      $year = $_GET['year'];
      $month = $_GET['month'];
   }
   else{
      $yearSql = "SELECT MAX(year) as 'year' FROM desco.`PUBLISHED_MOD`";   
      $resultYear = $mysqli -> query($yearSql);
      $year = $resultYear ->  fetch_assoc()['year'];

      $monthSql = "SELECT MAX(month) as 'month' FROM desco.`PUBLISHED_MOD` WHERE `year` = $year";
      $resultMonth = $mysqli -> query($monthSql);   
      $month = $resultMonth -> fetch_assoc()['month'];
   }
   $division = !empty($_GET['division']) ? $_GET['division'] : "DESCO";
?>
<h3 class="text-center">Monthly Operational Summary of <?php echo($division); echo ($division !="DESCO" ? " S & D Division" : ""); ?></h3>
<div class='row'>
    <div class='col'></div>
    <div class='col'>
        <h5 class="text-center">Month : <?php echo date('F', mktime(0, 0, 0, $month, 10)); ?></h5>
    </div>
    <div class='col'>
        <h5 class="text-center"> Year : <?php echo $year; ?></h5>
    </div>
    <div class='col'></div>
</div>

<div class="table-responsive mt-4 mx-3">
   <table class="table table-bordered border-secondary table-hover text-center align-middle">
      <thead>
         <tr>
            <th scope="col" rowspan="2">Month-Year</th>
            <th scope="col" colspan="3">No. of Consumers</th>
            <th scope="col" rowspan="2">Energy Import (MkWh)</th>
            <th scope="col" rowspan="2">Energy Sold (MkWh)</th>
            <th scope="col" rowspan="2">System Loss (%)</th>
            <th scope="col" rowspan="2">Billed Amount (MTk)</th>
            <th scope="col" rowspan="2">Sales Rate (Tk/Unit)</th>
            <th scope="col" colspan="3">Collection Amount (MTk)</th>
            <th scope="col" rowspan="2">Collection / Bill Ratio (%)</th>
            <th scope="col" rowspan="2">Collection / Income Ratio (%)</th>
            <th scope="col" rowspan="2">Account Receivables (MTk)</th>
            <th scope="col" rowspan="2">Account Receivables Equivalent Month</th>
         </tr>
         <tr>
            <th scope="col">Prepaid</th>
            <th scope="col">Postpaid</th>
            <th scope="col">Total</th>
            <th scope="col">Prepaid</th>
            <th scope="col">Postpaid</th>
            <th scope="col">Total</th>
         </tr>
      </thead>
      <?php 
         $result = MonthlyDataPull($year, $month, $division,$mysqli);
         $dataRow = $result -> fetch_array(MYSQLI_ASSOC);
         
         $resultPrepaid = MonthlyPrepaidDataPull($year,$month,$division,$mysqli);
         $prepaidData = $resultPrepaid -> fetch_array(MYSQLI_ASSOC);
     ?>
      <tbody>
         <!-- This Month -->
         <tr>
            <th scope="row"><?php echo date('M', mktime(0, 0, 0, $month, 10)) .'-'.substr($dataRow['year'],2,2) ?></th>
            <td rowspan="2"><?php echo $prepaidData['pre_consumer_no']?></td>
            <td rowspan="2"><?php echo $dataRow['consumer'] - $prepaidData['pre_consumer_no']?></td>
            <td rowspan="2"><?php echo number_format($dataRow['consumer']) ?></td>
            <td><?php echo number_format($dataRow['import_unit'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['sales_unit'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['system_loss'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['bill_amount'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['sales_rate'],2,".",",") ?></td>
            <td><?php echo number_format($prepaidData['pre_collection_amount'] /1000000,2,".",",")?></td>
            <td><?php echo number_format(($dataRow['collection_amount'] - $prepaidData['pre_collection_amount']) /1000000,2,".","," )?></td>
            <td><?php echo number_format($dataRow['collection_amount'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['bc_ratio'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ci_ratio'],2,".",",") ?></td>
            <td rowspan="2"><?php echo number_format($dataRow['acc_receivable_amnt'] /1000000,2,".",",")  ?></td>
            <td rowspan="2"><?php echo number_format($dataRow['acc_receivable_eqv_month'],2,".",",")  ?></td>
         </tr>
         <!-- This Month YTD -->
         <tr>
            <th scope="row">YTD <?php echo date('M', mktime(0, 0, 0, $month, 10)) .'-'.substr($dataRow['year'],2,2)?></th>
            <?php 
               $result = YTDDataPull($year, $month, $division,$mysqli);
               $dataRow = $result -> fetch_array(MYSQLI_ASSOC);
               
               $resultYTDPrepaid = YTDPrepaidDataPull($year, $month, $division, $mysqli);
               $ytdPrepaidData = $resultYTDPrepaid -> fetch_array(MYSQLI_ASSOC);                          
               ?>                     
            <td><?php echo number_format($dataRow['ytd_import_unit'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_sales_unit'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_system_loss'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_bill_amount'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_sales_rate'],2,".",","); ?></td>
            <td><?php echo number_format($ytdPrepaidData['YTD_Prepaid_Collection'] /1000000,2,".",",") ?> </td>
            <td><?php echo number_format(($dataRow['ytd_coll_amount']- $ytdPrepaidData['YTD_Prepaid_Collection']) /1000000,2,".",",");?></td>
            <td><?php echo number_format($dataRow['ytd_coll_amount'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_bc_ratio'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_ci_ratio'],2,".",",") ?></td>
         </tr>
         <?php 
            $result = MonthlyDataPull($year, $month-1, $division,$mysqli);
            $dataRow = $result -> fetch_array(MYSQLI_ASSOC);
            
            $resultPrepaid = MonthlyPrepaidDataPull($year,$month-1,$division,$mysqli);
            $prepaidData = $resultPrepaid -> fetch_array(MYSQLI_ASSOC);
         ?>         
          <!-- Previous Month -->
         <tr>
            <th scope="row"><?php echo date('M', mktime(0, 0, 0, $month-1, 10)) .'-'.substr($dataRow['year'],2,2) ?></th>
            <td rowspan="2"><?php echo $prepaidData['pre_consumer_no']?></td>
            <td rowspan="2"><?php echo $dataRow['consumer'] - $prepaidData['pre_consumer_no']?></td>
            <td rowspan="2"><?php echo number_format($dataRow['consumer']) ?></td>
            <td><?php echo number_format($dataRow['import_unit'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['sales_unit'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['system_loss'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['bill_amount'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['sales_rate'],2,".",",") ?></td>
            <td><?php echo number_format($prepaidData['pre_collection_amount'] /1000000,2,".",",")?></td>
            <td><?php echo number_format(($dataRow['collection_amount'] - $prepaidData['pre_collection_amount']) /1000000,2,".","," )?></td>
            <td><?php echo number_format($dataRow['collection_amount'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['bc_ratio'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ci_ratio'],2,".",",") ?></td>
            <td rowspan="2"><?php echo number_format($dataRow['acc_receivable_amnt'] /1000000,2,".",",")  ?></td>
            <td rowspan="2"><?php echo number_format($dataRow['acc_receivable_eqv_month'],2,".",",")  ?></td>
         </tr>
         <!-- Previous Month YTD -->
         <tr>
            <th scope="row">YTD <?php echo date('M', mktime(0, 0, 0, $month-1, 10)) .'-'.substr($dataRow['year'],2,2)?></th>
            <?php 
               $result = YTDDataPull($year, $month-1, $division,$mysqli);
               $dataRow = $result -> fetch_array(MYSQLI_ASSOC);
               
               $resultYTDPrepaid = YTDPrepaidDataPull($year,$month-1,$division,$mysqli);
               $ytdPrepaidData = $resultYTDPrepaid -> fetch_array(MYSQLI_ASSOC);           
            ?>                     
            <td><?php echo number_format($dataRow['ytd_import_unit'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_sales_unit'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_system_loss'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_bill_amount'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_sales_rate'],2,".",",") ?></td>
            <td><?php echo number_format($ytdPrepaidData['YTD_Prepaid_Collection'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format(($dataRow['ytd_coll_amount']- $ytdPrepaidData['YTD_Prepaid_Collection']) /1000000,2,".",",")?></td>
            <td><?php echo number_format($dataRow['ytd_coll_amount'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_bc_ratio'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_ci_ratio'],2,".",",") ?></td>
         </tr>
         <?php 
            $result = MonthlyDataPull($year-1, $month, $division,$mysqli);
            $dataRow = $result -> fetch_array(MYSQLI_ASSOC);
            
            $resultPrepaid = MonthlyPrepaidDataPull($year-1,$month,$division,$mysqli);
            $prepaidData = $resultPrepaid -> fetch_array(MYSQLI_ASSOC);
            ?>
            <!-- This Month Last year -->
         <tr>
            <th scope="row"><?php echo date('M', mktime(0, 0, 0, $month, 10)) .'-'.substr($dataRow['year'],2,2)?></th>
            <td rowspan="2"><?php echo $prepaidData['pre_consumer_no']?></td>
            <td rowspan="2"><?php echo $dataRow['consumer'] - $prepaidData['pre_consumer_no']?></td>
            <td rowspan="2"><?php echo number_format($dataRow['consumer']) ?></td>
            <td><?php echo number_format($dataRow['import_unit'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['sales_unit'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['system_loss'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['bill_amount'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['sales_rate'],2,".",",") ?></td>
            <td><?php echo number_format($prepaidData['pre_collection_amount'] /1000000,2,".",",")?></td>
            <td><?php echo number_format(($dataRow['collection_amount'] - $prepaidData['pre_collection_amount']) /1000000,2,".","," )?></td>
            <td><?php echo number_format($dataRow['collection_amount'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['bc_ratio'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ci_ratio'],2,".",",") ?></td>
            <td rowspan="2"><?php echo number_format($dataRow['acc_receivable_amnt'] /1000000,2,".",",")  ?></td>
            <td rowspan="2"><?php echo number_format($dataRow['acc_receivable_eqv_month'],2,".",",")  ?></td>
         </tr>
         <!-- This Month Last Year YTD -->
         <tr>
            <th scope="row">YTD <?php echo date('M', mktime(0, 0, 0, $month, 10)) .'-'.substr($dataRow['year'],2,2)?></th>
            <?php 
               $result = YTDDataPull($year-1, $month, $division,$mysqli);
               $dataRow = $result -> fetch_array(MYSQLI_ASSOC);
               
               $resultYTDPrepaid = YTDPrepaidDataPull($year-1,$month,$division,$mysqli);
               $ytdPrepaidData = $resultYTDPrepaid -> fetch_array(MYSQLI_ASSOC);
               ?>                     
            <td><?php echo number_format($dataRow['ytd_import_unit'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_sales_unit'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_system_loss'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_bill_amount'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_sales_rate'],2,".",",") ?></td>
            <td><?php echo number_format($ytdPrepaidData['YTD_Prepaid_Collection'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format(($dataRow['ytd_coll_amount']- $ytdPrepaidData['YTD_Prepaid_Collection']) /1000000,2,".",",")?></td>
            <td><?php echo number_format($dataRow['ytd_coll_amount'] /1000000,2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_bc_ratio'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_ci_ratio'],2,".",",") ?></td>
         </tr>
      </tbody>
      <tfoot></tfoot>
   </table>
</div>
<?php 
   function findDivIdByName($divisionName, $connection){
      $sql = "SELECT DIVISION_ID FROM desco.SALES_DISTRIBUTION_DIVISION WHERE DIVISION_NAME = '$divisionName'";
      $result = $connection -> query($sql) -> fetch_array(MYSQLI_ASSOC); 
      $divisionId = $result['DIVISION_ID'];
      return $divisionId;
   }

   function MonthlyDataPull($year, $month,$division,$connection){
      if($month <= 0){
         $month = 12;
         $year -= 1;
      }
      $sql="SELECT '$division' as `division_name`, IFNULL(`month`,$month) `month` , IFNULL(`year`, $year) `year`, IFNULL(`consumer`,0) consumer, IFNULL(`import_unit`,0) import_unit, IFNULL(`sales_unit`,0) sales_unit,
            IFNULL(`system_loss`,0) system_loss, IFNULL(`bill_amount`,0) bill_amount, IFNULL(`sales_rate`,0) sales_rate, IFNULL(`collection_amount`,0) collection_amount,
            IFNULL(`bc_ratio`,0) bc_ratio, IFNULL(`ci_ratio`,0) ci_ratio, IFNULL(`acc_receivable_amnt`,0) acc_receivable_amnt, IFNULL(`acc_receivable_eqv_month`,0) acc_receivable_eqv_month             
            FROM desco.`PUBLISHED_MOD` WHERE `year`= $year AND `month` = $month";
      $sql .= " AND `division_id` =". findDivIdByName($division, $connection);
   
      $resultSet = $connection -> query($sql);
      return $resultSet;
   }
   
   function YTDDataPull($year,$month,$division,$connection){
      if($month <= 0)  {
         $month = 12;
         $year -= 1;
      }
      $sql = "SELECT IFNULL(`ytd_import_unit`,0) ytd_import_unit, IFNULL(`ytd_sales_unit`, 0) ytd_sales_unit, IFNULL(`ytd_system_loss`,0) ytd_system_loss, IFNULL(`ytd_bill_amount`,0) ytd_bill_amount,
              IFNULL(`ytd_coll_amount`,0) ytd_coll_amount, IFNULL(`ytd_bc_ratio`,0) ytd_bc_ratio, IFNULL(`ytd_ci_ratio`,0) ytd_ci_ratio, IFNULL(`ytd_sales_rate`,0) ytd_sales_rate
              FROM desco.`PUBLISHED_MOD` WHERE `year`= $year AND `month` = $month";
      $sql .= " AND `division_id` = ". findDivIdByName($division, $connection);
      
      $resultSet = $connection -> query($sql);
      return $resultSet;
   }
   
   function MonthlyPrepaidDataPull($year, $month,$division,$connection){      
      if($month <= 0)  {
         $month = 12;
         $year -= 1;
      }
      $sql = "SELECT SUM(IFNULL(consumer_nos,0)) AS pre_consumer_no, SUM(IFNULL(net_collection,0)) AS pre_collection_amount FROM desco.`MOD_PREPAID_DATA` WHERE `YEAR` = $year AND `MONTH` = $month";      
      $sql .= " AND `DIVISION_ID` =" . findDivIdByName($division, $connection);      
      $resultSet = $connection -> query($sql);
      return $resultSet;
   }
   
   function YTDPrepaidDataPull($year,$month,$division,$connection){      
      if($month <= 0){
         $month = 12;
         $year -= 1;
      }
     $sql = "SELECT SUM(IFNULL(`net_collection`,0)) as YTD_Prepaid_Collection FROM desco.`MOD_PREPAID_DATA` WHERE ";
     switch ((string) $month){
         case '1':
             $sql .= " ((`year` = $year AND `month` = 1) OR (`year` = $year-1 AND `month` in(7,8,9,10,11,12)))";
             break;
         case '2':
             $sql .= " ((`year` = $year AND `month` in(1,2)) OR (`year` = $year-1 AND `month` in(7,8,9,10,11,12)))";
             break;
         case '3':
             $sql .= " ((`year` = $year AND `month` in(1,2,3)) OR (`year` = $year-1 AND `month` in(7,8,9,10,11,12)))";
             break;
         case '4':
             $sql .= " ((`year` = $year AND `month` in(1,2,3,4)) OR (`year` = $year-1 AND `month` in(7,8,9,10,11,12)))";
             break;
         case '5':
             $sql .= " ((`year` = $year AND `month` in(1,2,3,4,5)) OR (`year` = $year-1 AND `month` in(7,8,9,10,11,12)))";
             break;
         case '6':
             $sql .= " ((`year` = $year AND `month` in(1,2,3,4,5,6)) OR (`year` = $year-1 AND `month` in(7,8,9,10,11,12)))";
             break;
         case '7':
             $sql .= " `year` = $year AND `month` = 7";
             break;
         case '8':
             $sql .= " `year` = $year AND `month` in (7,8)";
             break;
         case '9':
             $sql .= " `year` = $year AND `month` in (7,8,9)";
             break;
         case '10':
             $sql .= " `year` = $year AND `month` in (7,8,9,10)";
             break;
         case '11':
             $sql .= " `year` = $year AND `month` in (7,8,9,10,11)";
             break;
         case '12':
             $sql .= " `year` = $year AND `month` in (7,8,9,10,11,12)";
             break;        
         default:
             $sql .=""; 
             break;
     }
     $sql .= " AND `DIVISION_ID` = ". findDivIdByName($division, $connection);      
      
     $resultSet = $connection -> query($sql);
     return $resultSet;
   }
?>