<?php
   if(!empty($_GET['year'])){
      $year = $_GET['year'];
      $month = $_GET['month'];
   }
   else{
      $yearSql = "SELECT MAX(year_mod) as 'year_mod' FROM `mod_snd_month_data`";   
      $resultYear = $mysqli -> query($yearSql);
      $year = $resultYear ->  fetch_assoc()['year_mod'];

      $monthSql = "SELECT MAX(month_sl) as 'month_sl' FROM `mod_snd_month_data` WHERE `year_mod` = $year";
      $resultMonth = $mysqli -> query($monthSql);   
      $month = $resultMonth -> fetch_assoc()['month_sl'];
   }
   $division = !empty($_GET['division']) ? $_GET['division'] : "DESCO";
?>
<h3 class="text-center">Operational Overview of <?php echo($division); echo ($division !="DESCO" ? " S & D Division" : ""); ?></h3>
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
         <tr>
            <th scope="row"><?php echo $dataRow['month_mod'].'-'.substr($dataRow['year_mod'],2,2) ?></th>
            <td rowspan="2"><?php echo $prepaidData['pre_consumer_no']?></td>
            <td rowspan="2"><?php echo $dataRow['consumer'] - $prepaidData['pre_consumer_no']?></td>
            <td rowspan="2"><?php echo number_format($dataRow['consumer']) ?></td>
            <td><?php echo number_format($dataRow['import_energy'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['sold_energy'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['system_loss'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['sales_mtk'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['selling_rate'],2,".",",") ?></td>
            <td><?php echo number_format($prepaidData['pre_collection_amount'],2,".",",")?></td>
            <td><?php echo number_format(($dataRow['col_mtk'] - $prepaidData['pre_collection_amount']),2,".","," )?></td>
            <td><?php echo number_format($dataRow['col_mtk'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['bc_ratio'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ci_ratio'],2,".",",") ?></td>
            <td rowspan="2"><?php echo number_format($dataRow['acc_receivable_amnt'],2,".",",")  ?></td>
            <td rowspan="2"><?php echo number_format($dataRow['acc_receivable_eqv_month'],2,".",",")  ?></td>
         </tr>
         <tr>
            <th scope="row">YTD <?php echo $dataRow['month_mod'].'-'.substr($dataRow['year_mod'],2,2)?></th>
            <?php 
               $result = YTDDataPull($year, $month, $division,$mysqli);
               $dataRow = $result -> fetch_array(MYSQLI_ASSOC);
               
               $resultYTDPrepaid = YTDPrepaidDataPull($year, $month, $division, $mysqli);
               $ytdPrepaidData = $resultYTDPrepaid -> fetch_array(MYSQLI_ASSOC);                          
               ?>                     
            <td><?php echo number_format($dataRow['ytd_energy_import_unit'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_energy_sales_unit'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_system_loss'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_bill_mtk'],2,".",",") ?></td>
            <td><?php echo number_format(($dataRow['ytd_bill_mtk']/$dataRow['ytd_energy_sales_unit']),2,".",","); ?></td>
            <td><?php echo number_format($ytdPrepaidData['YTD_Prepaid_Collection'],2,".",",") ?> </td>
            <td><?php echo number_format(($dataRow['ytd_col_mtk']- $ytdPrepaidData['YTD_Prepaid_Collection']),2,".",",");?></td>
            <td><?php echo number_format($dataRow['ytd_col_mtk'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_bc_ratio'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_ci_ratio'],2,".",",") ?></td>
         </tr>
         <?php 
            $result = MonthlyDataPull($year, $month-1, $division,$mysqli);
            $dataRow = $result -> fetch_array(MYSQLI_ASSOC);
            
            $resultPrepaid = MonthlyPrepaidDataPull($year,$month-1,$division,$mysqli);
            $prepaidData = $resultPrepaid -> fetch_array(MYSQLI_ASSOC);
         ?>                                         
         <tr>
            <th scope="row"><?php echo $dataRow['month_mod'].'-'.substr($dataRow['year_mod'],2,2) ?></th>
            <td rowspan="2"><?php echo $prepaidData['pre_consumer_no']?></td>
            <td rowspan="2"><?php echo $dataRow['consumer'] - $prepaidData['pre_consumer_no']?></td>
            <td rowspan="2"><?php echo number_format($dataRow['consumer']) ?></td>
            <td><?php echo number_format($dataRow['import_energy'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['sold_energy'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['system_loss'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['sales_mtk'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['selling_rate'],2,".",",") ?></td>
            <td><?php echo number_format($prepaidData['pre_collection_amount'],2,".",",")?></td>
            <td><?php echo number_format(($dataRow['col_mtk'] - $prepaidData['pre_collection_amount']),2,".","," )?></td>
            <td><?php echo number_format($dataRow['col_mtk'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['bc_ratio'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ci_ratio'],2,".",",") ?></td>
            <td rowspan="2"><?php echo number_format($dataRow['acc_receivable_amnt'],2,".",",")  ?></td>
            <td rowspan="2"><?php echo number_format($dataRow['acc_receivable_eqv_month'],2,".",",")  ?></td>
         </tr>
         <tr>
            <th scope="row">YTD <?php echo $dataRow['month_mod'].'-'.substr($dataRow['year_mod'],2,2)?></th>
            <?php 
               $result = YTDDataPull($year, $month-1, $division,$mysqli);
               $dataRow = $result -> fetch_array(MYSQLI_ASSOC);
               
               $resultYTDPrepaid = YTDPrepaidDataPull($year,$month-1,$division,$mysqli);
               $ytdPrepaidData = $resultYTDPrepaid -> fetch_array(MYSQLI_ASSOC);           
               ?>                     
            <td><?php echo number_format($dataRow['ytd_energy_import_unit'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_energy_sales_unit'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_system_loss'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_bill_mtk'],2,".",",") ?></td>
            <td><?php echo number_format(($dataRow['ytd_bill_mtk']/$dataRow['ytd_energy_sales_unit']),2,".",",") ?></td>
            <td><?php echo number_format($ytdPrepaidData['YTD_Prepaid_Collection'],2,".",",") ?></td>
            <td><?php echo number_format(($dataRow['ytd_col_mtk']- $ytdPrepaidData['YTD_Prepaid_Collection']),2,".",",")?></td>
            <td><?php echo number_format($dataRow['ytd_col_mtk'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_bc_ratio'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_ci_ratio'],2,".",",") ?></td>
         </tr>
         <?php 
            $result = MonthlyDataPull($year-1, $month, $division,$mysqli);
            $dataRow = $result -> fetch_array(MYSQLI_ASSOC);
            
            $resultPrepaid = MonthlyPrepaidDataPull($year-1,$month,$division,$mysqli);
            $prepaidData = $resultPrepaid -> fetch_array(MYSQLI_ASSOC);
            ?>
         <tr>
            <th scope="row"><?php echo $dataRow['month_mod'].'-'.substr($dataRow['year_mod'],2,2)?></th>
            <td rowspan="2"><?php echo $prepaidData['pre_consumer_no']?></td>
            <td rowspan="2"><?php echo $dataRow['consumer'] - $prepaidData['pre_consumer_no']?></td>
            <td rowspan="2"><?php echo number_format($dataRow['consumer']) ?></td>
            <td><?php echo number_format($dataRow['import_energy'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['sold_energy'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['system_loss'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['sales_mtk'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['selling_rate'],2,".",",") ?></td>
            <td><?php echo number_format($prepaidData['pre_collection_amount'],2,".",",")?></td>
            <td><?php echo number_format(($dataRow['col_mtk'] - $prepaidData['pre_collection_amount']),2,".","," )?></td>
            <td><?php echo number_format($dataRow['col_mtk'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['bc_ratio'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ci_ratio'],2,".",",") ?></td>
            <td rowspan="2"><?php echo number_format($dataRow['acc_receivable_amnt'],2,".",",")  ?></td>
            <td rowspan="2"><?php echo number_format($dataRow['acc_receivable_eqv_month'],2,".",",")  ?></td>
         </tr>
         <tr>
            <th scope="row">YTD <?php echo $dataRow['month_mod'].'-'.substr($dataRow['year_mod'],2,2)?></th>
            <?php 
               $result = YTDDataPull($year-1, $month, $division,$mysqli);
               $dataRow = $result -> fetch_array(MYSQLI_ASSOC);
               
               $resultYTDPrepaid = YTDPrepaidDataPull($year-1,$month,$division,$mysqli);
               $ytdPrepaidData = $resultYTDPrepaid -> fetch_array(MYSQLI_ASSOC);
               ?>                     
            <td><?php echo number_format($dataRow['ytd_energy_import_unit'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_energy_sales_unit'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_system_loss'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_bill_mtk'],2,".",",") ?></td>
            <td><?php echo number_format(($dataRow['ytd_bill_mtk']/$dataRow['ytd_energy_sales_unit']),2,".",",") ?></td>
            <td><?php echo number_format($ytdPrepaidData['YTD_Prepaid_Collection'],2,".",",") ?></td>
            <td><?php echo number_format(($dataRow['ytd_col_mtk']- $ytdPrepaidData['YTD_Prepaid_Collection']),2,".",",")?></td>
            <td><?php echo number_format($dataRow['ytd_col_mtk'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_bc_ratio'],2,".",",") ?></td>
            <td><?php echo number_format($dataRow['ytd_ci_ratio'],2,".",",") ?></td>
         </tr>
      </tbody>
      <tfoot></tfoot>
   </table>
</div>
<?php 
   function MonthlyDataPull($year, $month,$division,$connection){
      if($month <= 0){
         $month = 12;
         $year -= 1;
      }
      $sql="SELECT `snd_new`, `month_mod`, `year_mod`, `consumer`, `import_energy`, `sold_energy`, `system_loss`,`sales_mtk`,`selling_rate`,`col_mtk`,`bc_ratio`, `ci_ratio`, `acc_receivable_amnt`, `acc_receivable_eqv_month` FROM `mod_snd_month_data` WHERE `snd_new` = '$division' AND `year_mod`= $year AND `month_sl` = $month";    
      $resultSet = $connection -> query($sql);
      return $resultSet;
   }
   
   function YTDDataPull($year, $month,$division,$connection){
      if($month <= 0)  {
         $month = 12;
         $year -= 1;
      }
      $sql = "SELECT `ytd_energy_import_unit`, `ytd_energy_sales_unit`, `ytd_system_loss`,`ytd_bill_mtk`,`ytd_col_mtk`,`ytd_bc_ratio`,`ytd_ci_ratio` FROM `mod_snd_month_data` WHERE `snd_new` = '$division' AND `year_mod`= $year AND `month_sl` = $month";
      $resultSet = $connection -> query($sql);
      return $resultSet;
   }
   
   function MonthlyPrepaidDataPull($year, $month,$division,$connection){      
      if($month <= 0)  {
         $month = 12;
         $year -= 1;
      }
      $sql = "SELECT IFNULL(SUM(consumer_nos),0) AS pre_consumer_no, IFNULL((sum(net_collection)/1000000),0) AS pre_collection_amount FROM `MOD_PREPAID_DATA` WHERE `YEAR` = $year AND `MONTH` = $month";
      if($division != "DESCO" && $division != "Desco" && $division != "desco"){ 
         $sql .= " AND `DIVISION` = '$division'";      
      }
      $resultSet = $connection -> query($sql);
      return $resultSet;
   }
   
   function YTDPrepaidDataPull($year,$month,$division,$connection){      
      if($month <= 0){
         $month = 12;
         $year -= 1;
      }
     $sql = "SELECT IFNULL((sum(`net_collection`)/1000000),0) as YTD_Prepaid_Collection FROM `MOD_PREPAID_DATA` WHERE ";
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
     if($division != "DESCO" && $division != "Desco" && $division != "desco"){ 
         $sql .= " AND `DIVISION` = '$division'";      
      }
     $resultSet = $connection -> query($sql);
     return $resultSet;
   }
?>