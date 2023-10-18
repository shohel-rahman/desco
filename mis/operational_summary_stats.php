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
<h3 class="text-center">Operational Summary Statistics of <?php echo($division); echo ($division !="DESCO" ? " S & D Division" : ""); ?> (Last 03 Years)</h3>
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
   <table class="table table-bordered border-secondary text-center align-middle">
      <thead>
         <tr>
            <th scope="col" >Month-Year</th>
            <th scope="col" >No. of Consumers</th>
            <th scope="col" >Energy Import (KWh)</th>
            <th scope="col" >Energy Sold (KWh)</th>
            <th scope="col" >System Loss (%)</th>
            <th scope="col" >Billed Amount (Tk)</th>
            <th scope="col" >Sales Rate (Tk/Unit)</th>
            <th scope="col" >Collection Amount (Tk)</th>
            <th scope="col" >Collection / Bill Ratio (%)</th>
            <th scope="col" >Collection / Income Ratio (%)</th>
            <th scope="col" >Account Receivables (Tk)</th>
            <th scope="col" >Account Receivables Equivalent Month</th>
         </tr>
      </thead>
      <tbody>
      <?php 
         $result = MonthWiseSummary($year, $month, $division,$mysqli);          
         foreach($result as $dataRow){
            $printRow = 
                "<tr>
                    <th scope='row'>" . date('M', mktime(0, 0, 0, $dataRow['month'], 10)) .'-'.substr($dataRow['year'],2,2) . "</th>
                    <td>" . number_format($dataRow['consumer']) . "</td>
                    <td>" . number_format($dataRow['import_unit'],0,".",",") . "</td>
                    <td>" . number_format($dataRow['sales_unit'],0,".",",")  . "</td>
                    <td>" . number_format($dataRow['system_loss'],2,".",",") . "</td>
                    <td>" . number_format($dataRow['bill_amount'],0,".",",") . "</td>
                    <td>" . number_format($dataRow['sales_rate'],2,".",",") . "</td>
                    <td>" . number_format($dataRow['collection_amount'],0,".",",") . "</td>
                    <td>" . number_format($dataRow['bc_ratio'],2,".",",") . "</td>
                    <td>" . number_format($dataRow['ci_ratio'],2,".",",") . "</td>
                    <td>" . number_format($dataRow['acc_receivable_amnt'],0,".",",") . "</td>
                    <td>" . number_format($dataRow['acc_receivable_eqv_month'],2,".",",")  . "</td>               
                </tr>";
            echo $printRow;
            
            if ($dataRow['month'] == 6 or ($dataRow['month'] == $month and $dataRow['year'] == $year)){
               $printRow = 
                "<tr class='table-active'>
                    <th>FY " . substr($dataRow['financial_year'],2,3).''.substr($dataRow['financial_year'],-2) . "</th>
                    <th>" . number_format($dataRow['consumer']) . "</th>
                    <th>" . number_format($dataRow['ytd_import_unit'],0,".",",") . "</th>
                    <th>" . number_format($dataRow['ytd_sales_unit'],0,".",",")  . "</th>
                    <th>" . number_format($dataRow['ytd_system_loss'],2,".",",") . "</th>
                    <th>" . number_format($dataRow['ytd_bill_amount'],0,".",",") . "</th>
                    <th>" . number_format($dataRow['ytd_sales_rate'],2,".",",") . "</th>
                    <th>" . number_format($dataRow['ytd_coll_amount'],0,".",",") . "</th>
                    <th>" . number_format($dataRow['ytd_bc_ratio'],2,".",",") . "</th>
                    <th>" . number_format($dataRow['ytd_ci_ratio'],2,".",",") . "</th>
                    <th>" . number_format($dataRow['acc_receivable_amnt'],0,".",",") . "</th>
                    <th>" . number_format($dataRow['acc_receivable_eqv_month'],2,".",",")  . "</th>               
                </tr>";
               echo $printRow;
            }
         }
     ?>
      </tbody>
      <tfoot></tfoot>
   </table>
</div>
<?php 
   function MonthWiseSummary($year, $month,$division,$connection){
      if($month >= 7){
         $reqrdFinYear = "'".strval($year)."-".strval($year+1)."',";
         $reqrdFinYear .= "'" .strval($year-1) ."-". strval($year) ."',";
         $reqrdFinYear .= "'" .strval($year-2) ."-". strval($year-1) ."',";
         $reqrdFinYear .= "'" .strval($year-3) ."-". strval($year-2)."'";
      }
      else{
         $reqrdFinYear = "'".strval($year-1) ."-". strval($year) ."',";
         $reqrdFinYear .= "'".strval($year-2) ."-". strval($year-1) ."',";
         $reqrdFinYear .= "'".strval($year-3) ."-". strval($year-2) ."',";
         $reqrdFinYear .= "'".strval($year-4) ."-". strval($year-3) ."'";
      }
      $sql="SELECT  `month`, `year`, `consumer`, `import_unit`, `sales_unit`, `system_loss`,`bill_amount`,`sales_rate`,`collection_amount`,`bc_ratio`, `ci_ratio`,
      `acc_receivable_amnt`, `acc_receivable_eqv_month`, `ytd_import_unit`,`ytd_sales_unit`, `ytd_system_loss`, `ytd_bill_amount`,`ytd_coll_amount`,`ytd_ci_ratio`,
      `ytd_bc_ratio`, `ytd_sales_rate`, `financial_year` FROM desco.`PUBLISHED_MOD` 
       WHERE  `financial_year` IN ($reqrdFinYear) AND division_id = ". findDivIdByName($division, $connection)." ORDER BY year,  month";      
      $resultSet = $connection -> query($sql);
      return $resultSet;
   }
?>