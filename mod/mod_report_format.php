<!DOCTYPE html>
<html>
  <head>   
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">    
    <title>MOD Reports | DESCO</title>
  </head>
  <?php
      require('../../../../opt/config/connection.php');
      if(!empty($_GET['year'])){
         $year = $_GET['year'];
         $month = $_GET['month'];
      }
      else{
         $yearSql = "SELECT MAX(year) as 'year' FROM desco.`PUBLISHED_MOD`";   
         $resultYear = $mysqli -> query($yearSql);
         $year = $resultYear -> fetch_assoc()['year'];

         $monthSql = "SELECT MAX(month) as 'month' FROM desco.`PUBLISHED_MOD` WHERE `year` = $year";
         $resultMonth = $mysqli -> query($monthSql);   
         $month = $resultMonth -> fetch_assoc()['month'];
      }
      $division = !empty($_GET['division']) ? $_GET['division'] : "DESCO";
   ?>
   <body style="width:1450px">
      <h2 class="text-center">Dhaka Electric Supply Company Limited</h2>
      <h3 class="text-center">Monthly Operational Data</h3>
     <div class='row'>
         <div class='col'>            
            <h6 class="px-2">Report Date: ....../....../<?php echo $year; ?></h6>
            <h6 class="px-2">Ref. No.:27.24.0000.022.99.001.<?php echo date('y', mktime(10,12,6,5,7,$year)); ?>.   </h6>
         </div>
         <div class='col'></div>
         <div class='col'></div>
         <div class='col'>
            <h5 class="text-right">Month: <?php echo date('F', mktime(0, 0, 0, $month, 10)); ?> - <?php echo $year; ?></h5>
         </div>
      </div>

      <div class="table-responsive mt-4 mx-3">
         <table class="table-bordered border-secondary table-hover text-center align-middle px-0 py-0">
            <thead>
               <tr>
                  <th  style="width:8% rowspan=2">S & D Division</th>
                  <th  style="width:10% rowspan=2">Timeline</th>
                  <th  style="width:8% rowspan=2">No. of Consumer</th>
                  <th  style="width:8% rowspan=2">Energy Import (MkWh)</th>
                  <th  style="width:8% rowspan=2">Energy Sales (MkWh)</th>
                  <th  style="width:4% rowspan=2">System Loss (%)</th>
                  <th  style="width:8% rowspan=2">Billed Amount (MTk)</th>
                  <th  style="width:4% rowspan=2">Sales Rate (Tk/Unit)</th>
                  <th  style="width:8% rowspan=2">Collection Amount (MTk)</th>
                  <th  style="width:5% rowspan=2">Collection / Bill Ratio (%)</th>
                  <th  style="width:5% rowspan=2">Collection / Import Ratio (%)</th>
                  <th  style="width:8% rowspan=2">Account Receivables (MTk)</th>
                  <th  style="width:8% rowspan=2">Account Receivables Equivalent Month</th>
                  <th  style="width:6% rowspan=2">Maximum Demand (MW)</th>
               </tr>
            </thead>               
            <tbody>
               <?php
                  $resultSet = getDivCirZone($mysqli);
                  $divisions = array();
                  $circles = array();
                  $zones = array();
                  while($result = $resultSet->fetch_array(MYSQLI_ASSOC)){
                     array_push($divisions, $result['DIVISION_ID']);
                     array_push($circles, $result['CIRCLE_ID']);
                     array_push($zones, $result['ZONE_ID']);
                  }

                  $divId = 0;
                  $circleId = 0;
                  $zoneId = 0;                   
                  $ptrCircleRow = false;
                  $ptrZoneRow = false;
                  

                  for($i=0, $d=0, $c=0, $z=0; $i<36; $i++){                    
                     if($c>0 && $circles[$c] <> $circles[$c-1]){                        
                        $ptrCircleRow =true;
                        $circleId = $circles[$c-1];
                        $circles = array_slice($circles, $c);
                        $c=0;
                     }
                     else if($zones[$z] <> $zones[$z-1] && $z>0){                        
                        $ptrZoneRow = true;
                        $zoneId = $zones[$z-1];                      
                        $zones = array_slice($zones, $z);
                        $z=0;
                     }
                     else{
                        $divId = $divisions[$d];
                        $d++;
                        $c++;
                        $z++;
                        $circleId = 0;
                        $zoneId = 0; 
                        $ptrCircleRow = false;
                        $ptrZoneRow = false;
                     }
                     
                     // <!-- First Row Current Month -->
                     if($ptrCircleRow){                 
                        $currentMonth = CircleMonthlyDataPull($year,$month, $circleId, $mysqli)->fetch_array(MYSQLI_ASSOC);                        
                     }
                     else if($ptrZoneRow){
                        $currentMonth = ZoneMonthlyDataPull($year,$month, $zoneId, $mysqli)->fetch_array(MYSQLI_ASSOC);                                               
                     }                                       
                     else $currentMonth = MonthlyDataPull($year,$month, $divId, $mysqli)->fetch_array(MYSQLI_ASSOC);
                     $monthData = "<tr style='height:25px'>                     
                        <th scope='row' rowspan=5 style='height:125px'>". $currentMonth['DIVISION_NAME']."</th>
                        <th>".  date('M', mktime(0, 0, 0, $month, 10)) .'-'. date('y', mktime(10,12,6,5,7,$year)) ."</th>
                        <td>".  number_format($currentMonth['consumer'])."</td>
                        <td>".  number_format($currentMonth['import_unit'] /1000000,3,'.',',') ."</td>
                        <td>".  number_format($currentMonth['sales_unit'] /1000000,3,'.',',')."</td>
                        <td>".  number_format($currentMonth['system_loss'],2,'.',',')."</td>
                        <td>".  number_format($currentMonth['bill_amount'] /1000000,3,'.',',') ."</td>
                        <td>".  number_format($currentMonth['sales_rate'],2,'.',','). "</td>
                        <td>".  number_format($currentMonth['collection_amount'] /1000000,3,'.',',')."</td>
                        <td>".  number_format($currentMonth['bc_ratio'],2,'.',',')."</td>
                        <td>".  number_format($currentMonth['ci_ratio'],2,'.',',')."</td>
                        <td>".  number_format($currentMonth['acc_receivable_amnt'] /1000000,3,'.',',')."</td>
                        <td>".  zereValueCheck(number_format($currentMonth['acc_receivable_eqv_month'],2,'.',','))."</td>
                        <td>".  zereValueCheck(number_format($currentMonth['MAX_LOAD'],2,'.',','))."</td>
                     </tr>";
                     echo $monthData;
                                   
                  
   
                     // <!-- Second Row Previous Month -->
                     if($ptrCircleRow){
                        $previousMonth = CircleMonthlyDataPull($year,$month-1, $circleId, $mysqli)->fetch_array(MYSQLI_ASSOC);
                     }
                     else if($ptrZoneRow){
                       $previousMonth = ZoneMonthlyDataPull($year,$month-1, $zoneId, $mysqli)->fetch_array(MYSQLI_ASSOC);    
                     }                    
                     else $previousMonth = MonthlyDataPull($year,$month-1, $divId, $mysqli)->fetch_array(MYSQLI_ASSOC);                     
                     $monthData = "<tr style='height:25px'>
                        <td>".  date('M', mktime(0, 0, 0, $previousMonth['month'], 10)) .'-'. date('y', mktime(10,12,6,5,7,$year)) ."</td>
                        <td>".  number_format($previousMonth['consumer'])."</td>
                        <td>".  number_format($previousMonth['import_unit'] /1000000,3,'.',',') ."</td>
                        <td>".  number_format($previousMonth['sales_unit'] /1000000,3,'.',',')."</td>
                        <td>".  number_format($previousMonth['system_loss'],2,'.',',')."</td>
                        <td>".  number_format($previousMonth['bill_amount'] /1000000,3,'.',',') ."</td>
                        <td>".  number_format($previousMonth['sales_rate'],2,'.',','). "</td>
                        <td>".  number_format($previousMonth['collection_amount'] /1000000,3,'.',',')."</td>
                        <td>".  number_format($previousMonth['bc_ratio'],2,'.',',')."</td>
                        <td>".  number_format($previousMonth['ci_ratio'],2,'.',',')."</td>
                        <td>".  number_format($previousMonth['acc_receivable_amnt'] /1000000,3,'.',',')."</td>
                        <td>".  zereValueCheck(number_format($previousMonth['acc_receivable_eqv_month'],2,'.',','))."</td>
                        <td>".  zereValueCheck(number_format($previousMonth['MAX_LOAD'],2,'.',','))."</td>
                     </tr>";
                     echo $monthData;
                      
                                  
                     // <!-- Third Row This Month Last Year -->
                     if($ptrCircleRow){
                        $monthLastYr = CircleMonthlyDataPull($year-1,$month, $circleId, $mysqli)->fetch_array(MYSQLI_ASSOC);
                     }
                     else if($ptrZoneRow){
                       $monthLastYr = ZoneMonthlyDataPull($year-1,$month, $zoneId, $mysqli)->fetch_array(MYSQLI_ASSOC);    
                     }                    
                     else $monthLastYr = MonthlyDataPull($year-1,$month, $divId, $mysqli)->fetch_array(MYSQLI_ASSOC);
                     $monthData = "<tr style='height:25px'>
                        <td>".  date('M', mktime(0, 0, 0, $month)) .'-'. date('y', mktime(10,12,6,5,7,$year-1)) ."</td>
                        <td>".  number_format($monthLastYr['consumer'])."</td>
                        <td>".  number_format($monthLastYr['import_unit'] /1000000,3,'.',',') ."</td>
                        <td>".  number_format($monthLastYr['sales_unit'] /1000000,3,'.',',')."</td>
                        <td>".  number_format($monthLastYr['system_loss'],2,'.',',')."</td>
                        <td>".  number_format($monthLastYr['bill_amount'] /1000000,3,'.',',') ."</td>
                        <td>".  number_format($monthLastYr['sales_rate'],2,'.',','). "</td>
                        <td>".  number_format($monthLastYr['collection_amount'] /1000000,3,'.',',')."</td>
                        <td>".  number_format($monthLastYr['bc_ratio'],2,'.',',')."</td>
                        <td>".  number_format($monthLastYr['ci_ratio'],2,'.',',')."</td>
                        <td>".  number_format($monthLastYr['acc_receivable_amnt'] /1000000,3,'.',',')."</td>
                        <td>".  zereValueCheck(number_format($monthLastYr['acc_receivable_eqv_month'],2,'.',','))."</td>
                        <td>".  zereValueCheck(number_format($monthLastYr['MAX_LOAD'],2,'.',','))."</td>
                     </tr>";
                     echo $monthData;    
                                
                  
                     // <!-- Fourth Row Year To Date Last Year -->
                     if($ptrCircleRow){                        
                        $lastYrYTD = CircleYTDDtaPull($year-1,$month, $circleId, $mysqli)->fetch_array(MYSQLI_ASSOC);
                     }
                     else if($ptrZoneRow){                        
                        $lastYrYTD = ZoneYTDDtaPull($year-1,$month, $zoneId, $mysqli)->fetch_array(MYSQLI_ASSOC);
                     }
                     
                     else $lastYrYTD = YTDDataPull($year-1,$month, $divId, $mysqli)->fetch_array(MYSQLI_ASSOC);                     
                     $fyTimeString =$month<7?date('y', mktime(10,12,6,5,7,$year-2))."-".date('y', mktime(10,12,6,5,7,$year-1)):date('y', mktime(10,12,6,5,7,$year-1))."-".date('y', mktime(10,12,6,5,7,$year));
                     $ytdData = "<tr style='height:25px'>
                        <td class='px-0'>FY ". $fyTimeString ."</td>
                        <td>".  number_format($monthLastYr['consumer'])."</td>
                        <td>".  number_format($lastYrYTD['ytd_import_unit'] /1000000,3,'.',',') ."</td>
                        <td>".  number_format($lastYrYTD['ytd_sales_unit'] /1000000,3,'.',',')."</td>
                        <td>".  number_format($lastYrYTD['ytd_system_loss'],2,'.',',')."</td>
                        <td>".  number_format($lastYrYTD['ytd_bill_amount'] /1000000,3,'.',',') ."</td>
                        <td>".  number_format($lastYrYTD['YTD_SALES_RATE'],2,'.',','). "</td>
                        <td>".  number_format($lastYrYTD['ytd_coll_amount'] /1000000,3,'.',',')."</td>
                        <td>".  number_format($lastYrYTD['ytd_bc_ratio'],2,'.',',')."</td>
                        <td>".  number_format($lastYrYTD['ytd_ci_ratio'],2,'.',',')."</td>
                        <td>".  number_format($monthLastYr['acc_receivable_amnt'] /1000000,3,'.',',')."</td>
                        <td>".  zereValueCheck(number_format($monthLastYr['acc_receivable_eqv_month'],2,'.',','))."</td>
                        <td>".  zereValueCheck(number_format($lastYrYTD['MAX_LOAD'],2,'.',','))."</td>
                     </tr>";
                     echo $ytdData;              
                     
                     // <!-- Fifth Row Year To Date -->
                     if($ptrCircleRow){
                        // echo "circle id is ".$circleId."<br/>"; date('y', mktime(10,12,6,5,7,$year-1)) ."-". date('y', mktime(10,12,6,5,7,$year)):date('y', mktime(10,12,6,5,7,$year)) ."-". date('y', mktime(10,12,6,5,7,$year+1))
                        $currentMonthYTD = CircleYTDDtaPull($year,$month, $circleId, $mysqli)->fetch_array(MYSQLI_ASSOC);
                     }
                     else if($ptrZoneRow){
                        $currentMonthYTD = ZoneYTDDtaPull($year,$month, $zoneId, $mysqli)->fetch_array(MYSQLI_ASSOC);
                     }                    
                     else $currentMonthYTD = YTDDataPull($year,$month, $divId, $mysqli)->fetch_array(MYSQLI_ASSOC);

                     $fyTimeString = $month<7?date('y', mktime(10,12,6,5,7,$year-1))."-".date('y', mktime(10,12,6,5,7,$year)):date('y', mktime(10,12,6,5,7,$year))."-".date('y', mktime(10,12,6,5,7,$year+1));
                     $ytdData = "<tr class='px-0 py-0' style='height:25px'>
                        <th class='px-0 py-0'>FY ". $fyTimeString ."</th>
                        <td>".  number_format($currentMonth['consumer'])."</td>
                        <td>".  number_format($currentMonthYTD['ytd_import_unit'] /1000000,3,'.',',') ."</td>
                        <td>".  number_format($currentMonthYTD['ytd_sales_unit'] /1000000,3,'.',',')."</td>
                        <td>".  number_format($currentMonthYTD['ytd_system_loss'],2,'.',',')."</td>
                        <td>".  number_format($currentMonthYTD['ytd_bill_amount'] /1000000,3,'.',',') ."</td>
                        <td>".  number_format($currentMonthYTD['YTD_SALES_RATE'],2,'.',','). "</td>
                        <td>".  number_format($currentMonthYTD['ytd_coll_amount'] /1000000,3,'.',',')."</td>
                        <td>".  number_format($currentMonthYTD['ytd_bc_ratio'],2,'.',',')."</td>
                        <td>".  number_format($currentMonthYTD['ytd_ci_ratio'],2,'.',',')."</td>
                        <td>".  number_format($currentMonth['acc_receivable_amnt'] /1000000,3,'.',',')."</td>
                        <td>".  zereValueCheck(number_format($currentMonth['acc_receivable_eqv_month'],2,'.',','))."</td>
                        <td>".  zereValueCheck(number_format($currentMonthYTD['MAX_LOAD'],2,'.',','))."</td>
                     </tr>";
                     echo $ytdData;
                     echo "<tr>
                           <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>";
                     unset($previousMonth);
                     unset($currentMonth);
                     unset($monthLastYr); 
                     unset($lastYrYTD);
                     unset($currentMonthYTD);
                     $ptrZoneRow = false;
                     $ptrCircleRow =false;
                     // // echo "<div id='paginationParent' class='d-none d-print-block'><div id ='pagination' class='d-none d-print-block'></div></div>";
                     // if($i <>0 && $i%4==0){
                     //    echo "<tr id='paginationParent' class='d-none d-print-block'>
                     //          <td id ='pagination' class='d-none d-print-block'></td>
                     //       </tr>";
                     // }
                  }                               
               ?>
               
            </tbody>
            <tfoot></tfoot>
         </table>
      </div>
   </body>   
</html>

<style>
   @media print {
      table { page-break-after:auto }
      tr    { page-break-inside:avoid; page-break-after:auto }
      td    { page-break-inside:avoid; page-break-after:auto }
      thead { display:table-header-group;}
      tfoot { display:table-footer-group;}
      /* tfoot:after{ 
         
         content: 'Page ' counter(page) ' of ' counter(pages);
         margin:0;
         padding:0;
         counter-increment: page;
         content-alignment:right;
      } */

      /* thead:after{          
         content: 'Page ' counter(page) ' of ' counter(pages);
         counter-increment: page;
         margin:0;
         padding:0;

      } */
      /* body{counter-reset: page 0;} */
   }
</style>
<script type="text/javascript">
          window.onload = addPageNumbers;

          function addPageNumbers() {
            // var totalPages = Math.ceil(document.body.scrollHeight / 842);  //842px A4 pageheight for 72dpi, 1123px A4 pageheight for 96dpi, 
            //  totalPages = 7;
            // console.log(totalPages);
            // for (var i = 0; i < totalPages; i++) {
            //   var pageNumberDiv = document.createElement("div");
            //   var pageNumber = document.createTextNode("Page " + (i+1) + " of " + totalPages);
            //   pageNumberDiv.style.position = "absolute";
            //   pageNumberDiv.style.top = "calc((" + i + " * (297mm - 0.5px)) - 40px)"; //297mm A4 pageheight; 0,5px unknown needed necessary correction value; additional wanted 40px margin from bottom(own element height included)
            //   pageNumberDiv.style.top = "calc((" + i + " * (842px - 0.5px)) - 40px)";
            //   pageNumberDiv.style.height = "16px";
            //   pageNumberDiv.appendChild(pageNumber);


            //   const parentElement = document.getElementById("paginationParent");
            //   document.getElementById("pagination").appendChild(pageNumber);
            //   parentElement.insertBefore(pageNumberDiv, document.getElementById("pagination"));           
            //   pageNumberDiv.style.left = "calc(100% - (" + pageNumberDiv.offsetWidth + "px + 20px))";
            // }
          }
</script>

<?php
	echo "<br /><br /><br /><div align='right'>Engr. Shamim Ahsan Chowdhury </br>Chief Engineer<br />ICT Division</div>";
	echo "Copy to:";
	echo "</br>1. Managing Director";
	echo "</br>2. Executive Director (Operation/Engineering/Procurement/Administration & HR/Finacne & Accoutns)";
	echo "</br>3. Chief Engineer (ICT/ P&D/ Network Operation/ D&P/ Procurement/ S&D Operation), GM (F&A/ Administration)";
	echo "</br>4. Superintending Engineer (P&D/ MP&S/ PI&T/ D&MP/ ICT/ Internal Audit/ S&D Operation, Project directors, Company Secretary)";
	echo "</br>5. All Divisional Head S&D Division, Executive Engineer (Testing & Repair/ Meter Plant/ Training & Dev/ Central Store/ Monitoring Cell/ Central Store/ MVSSMC)";
?>

<?php 

function zereValueCheck($val){
 if ($val <> 0) return $val;
 else return '--';
}

function ZoneMonthlyDataPull($year, $month,$zoneId,$connection){
   if($month <= 0)  {
      $month = 12;
      $year -= 1;
   }
   $sql = "SELECT ZONE.ZONE_NAME DIVISION_NAME, IFNULL(PUBLISHED_MOD.`year`, $year) `year`, IFNULL(PUBLISHED_MOD.`month`, $month) `month`, SUM(`consumer`) consumer, SUM(`import_unit`) import_unit, 
   sum(`sales_unit`) sales_unit, (SUM(`import_unit`)- sum(`sales_unit`))/ SUM(`import_unit`)* 100 system_loss, SUM(`bill_amount`) bill_amount, 
   SUM(`bill_amount`)/sum(`sales_unit`) sales_rate,  SUM(`collection_amount`) collection_amount, SUM(`collection_amount`)/ SUM(`bill_amount`)* 100 bc_ratio,
   (1 -(SUM(`import_unit`)- sum(`sales_unit`))/ SUM(`import_unit`))* SUM(`collection_amount`)/ SUM(`bill_amount`)* 100 ci_ratio, 
   SUM(`acc_receivable_amnt`) acc_receivable_amnt, 0 AS acc_receivable_eqv_month, 0 as MAX_LOAD 
  FROM desco.`PUBLISHED_MOD`, desco.SALES_DISTRIBUTION_DIVISION, desco.CIRCLE, desco.ZONE
  WHERE PUBLISHED_MOD.year = $year AND PUBLISHED_MOD.month = $month AND ZONE.ZONE_ID = $zoneId AND SALES_DISTRIBUTION_DIVISION.DIVISION_ID = PUBLISHED_MOD.division_id AND 
  SALES_DISTRIBUTION_DIVISION.CIRCLE_ID = CIRCLE.CIRCLE_ID AND ZONE.ZONE_ID =CIRCLE.ZONE_ID";
   $resultSet = $connection -> query($sql);
   return $resultSet;
}

function CircleMonthlyDataPull($year, $month,$circleId,$connection){
      if($month <= 0)  {
         $month = 12;
         $year -= 1;
      }
      $sql = "SELECT CIRCLE.CIRCLE_NAME DIVISION_NAME, IFNULL(PUBLISHED_MOD.`year`, $year) `year`, IFNULL(PUBLISHED_MOD.`month`,$month) `month`, SUM(`consumer`) consumer, SUM(`import_unit`) import_unit, 
              sum(`sales_unit`) sales_unit, (SUM(`import_unit`)- sum(`sales_unit`))/ SUM(`import_unit`)* 100 system_loss, SUM(`bill_amount`) bill_amount, 
              SUM(`bill_amount`)/sum(`sales_unit`) sales_rate,  SUM(`collection_amount`) collection_amount, SUM(`collection_amount`)/ SUM(`bill_amount`)* 100 bc_ratio,
              (1 -(SUM(`import_unit`)- sum(`sales_unit`))/ SUM(`import_unit`))* SUM(`collection_amount`)/ SUM(`bill_amount`)* 100 ci_ratio, 
              SUM(`acc_receivable_amnt`) acc_receivable_amnt, 0 AS acc_receivable_eqv_month, 0 as MAX_LOAD 
             FROM desco.`PUBLISHED_MOD`, desco.SALES_DISTRIBUTION_DIVISION, desco.CIRCLE 
             WHERE PUBLISHED_MOD.year = $year AND PUBLISHED_MOD.month = $month AND CIRCLE.CIRCLE_ID = $circleId AND SALES_DISTRIBUTION_DIVISION.DIVISION_ID = PUBLISHED_MOD.division_id AND 
             SALES_DISTRIBUTION_DIVISION.CIRCLE_ID = CIRCLE.CIRCLE_ID";
      $resultSet = $connection -> query($sql);
      return $resultSet;
   }

   function getDivCirZone($connection){
      $sql = "SELECT SALES_DISTRIBUTION_DIVISION.DIVISION_ID, SALES_DISTRIBUTION_DIVISION.DIVISION_NAME, CIRCLE.CIRCLE_ID, CIRCLE.CIRCLE_NAME,  ZONE.ZONE_ID, 
             ZONE.ZONE_NAME FROM desco.`SALES_DISTRIBUTION_DIVISION`, desco.CIRCLE, desco.ZONE
             WHERE SALES_DISTRIBUTION_DIVISION.CIRCLE_ID = CIRCLE.CIRCLE_ID AND CIRCLE.ZONE_ID = ZONE.ZONE_ID
             ORDER BY  ZONE.ZONE_ID, CIRCLE.CIRCLE_NAME,  SALES_DISTRIBUTION_DIVISION.DIVISION_NAME";
      $resultSet = $connection -> query($sql);
      return $resultSet;
      
   }

   function MonthlyDataPull ($year, $month,$divisionID,$connection){         
      if($month <= 0)  {
         $month = 12;
         $year -= 1;
      }
      $sql = "SELECT PUBLISHED_MOD.`division_id`, SALES_DISTRIBUTION_DIVISION.DIVISION_NAME, IFNULL(PUBLISHED_MOD.`year`, $year) `year`, IFNULL(PUBLISHED_MOD.`month`,$month) `month`, 
             `consumer`, `import_unit`, `sales_unit`, `system_loss`, `bill_amount`, `sales_rate`, `collection_amount`, `bc_ratio`, `ci_ratio`, `acc_receivable_amnt`, 
             `acc_receivable_eqv_month`, MAXIMUM_LOAD_DEMAND.MAX_LOAD  
            FROM desco.`PUBLISHED_MOD`, desco.MAXIMUM_LOAD_DEMAND, desco.SALES_DISTRIBUTION_DIVISION  WHERE PUBLISHED_MOD.year = $year AND PUBLISHED_MOD.month= $month 
            AND PUBLISHED_MOD.division_id=$divisionID AND PUBLISHED_MOD.division_id = MAXIMUM_LOAD_DEMAND.DIVISION_ID AND PUBLISHED_MOD.year =MAXIMUM_LOAD_DEMAND.YEAR 
            AND PUBLISHED_MOD.month = MAXIMUM_LOAD_DEMAND.MONTH AND SALES_DISTRIBUTION_DIVISION.DIVISION_ID = PUBLISHED_MOD.division_id";         
      $resultSet = $connection -> query($sql);
      return $resultSet;
   }

   function ZoneYTDDtaPull($year, $month,$zoneId,$connection){
      if($month <= 0)  {
         $month = 12;
         $year -= 1;
      }

      $sql = "SELECT ZONE.ZONE_NAME DIVISION_NAME, PUBLISHED_MOD.`year`, PUBLISHED_MOD.`month`,  SUM(`ytd_import_unit`) ytd_import_unit, 
            sum(`ytd_sales_unit`) ytd_sales_unit, (SUM(`ytd_import_unit`)- sum(`ytd_sales_unit`))/ SUM(`ytd_import_unit`)* 100 ytd_system_loss, SUM(`ytd_bill_amount`) ytd_bill_amount, 
            SUM(`ytd_bill_amount`)/sum(`ytd_sales_unit`) YTD_SALES_RATE,  SUM(`ytd_coll_amount`) ytd_coll_amount, SUM(`ytd_coll_amount`)/ SUM(`ytd_bill_amount`)* 100 ytd_bc_ratio,
            (1 -(SUM(`ytd_import_unit`)- sum(`ytd_sales_unit`))/ SUM(`ytd_import_unit`))* SUM(`ytd_coll_amount`)/ SUM(`ytd_bill_amount`)* 100 ytd_ci_ratio, 
            SUM(`acc_receivable_amnt`) acc_receivable_amnt, 0 AS acc_receivable_eqv_month, 0 as MAX_LOAD 
            FROM desco.`PUBLISHED_MOD`, desco.SALES_DISTRIBUTION_DIVISION, desco.CIRCLE, desco.ZONE
            WHERE PUBLISHED_MOD.year = $year AND PUBLISHED_MOD.month = $month AND ZONE.ZONE_ID = $zoneId AND SALES_DISTRIBUTION_DIVISION.DIVISION_ID = PUBLISHED_MOD.division_id AND 
            SALES_DISTRIBUTION_DIVISION.CIRCLE_ID = CIRCLE.CIRCLE_ID AND ZONE.ZONE_ID =CIRCLE.ZONE_ID";

      $resultSet = $connection -> query($sql);
      return $resultSet;
   }

   function CircleYTDDtaPull($year, $month,$circleId,$connection){
      if($month <= 0)  {
         $month = 12;
         $year -= 1;
      }

      $sql="SELECT CIRCLE.CIRCLE_NAME DIVISION_NAME, PUBLISHED_MOD.`year`, PUBLISHED_MOD.`month`, SUM(`ytd_import_unit`) ytd_import_unit, sum(`ytd_sales_unit`) ytd_sales_unit,
           (SUM(`ytd_import_unit`)- sum(`ytd_sales_unit`))/ SUM(`ytd_import_unit`)* 100 ytd_system_loss, SUM(`ytd_bill_amount`) ytd_bill_amount, 
           SUM(`ytd_bill_amount`)/sum(`ytd_sales_unit`) YTD_SALES_RATE, SUM(`ytd_coll_amount`) ytd_coll_amount, SUM(`ytd_coll_amount`)/ SUM(`ytd_bill_amount`)* 100 ytd_bc_ratio, 
           (1 -(SUM(`ytd_import_unit`)- sum(`ytd_sales_unit`))/ SUM(`ytd_import_unit`))* SUM(`ytd_coll_amount`)/ SUM(`ytd_bill_amount`)* 100 ytd_ci_ratio, 
           SUM(`acc_receivable_amnt`) acc_receivable_amnt, 0 AS acc_receivable_eqv_month, 0 as MAX_LOAD 
           FROM desco.`PUBLISHED_MOD`, desco.SALES_DISTRIBUTION_DIVISION, desco.CIRCLE 
           WHERE PUBLISHED_MOD.year = $year AND PUBLISHED_MOD.month = $month AND CIRCLE.CIRCLE_ID = $circleId AND SALES_DISTRIBUTION_DIVISION.DIVISION_ID = PUBLISHED_MOD.division_id 
           AND SALES_DISTRIBUTION_DIVISION.CIRCLE_ID = CIRCLE.CIRCLE_ID";

      $resultSet = $connection -> query($sql);
      return $resultSet;
   }
   
   function YTDDataPull($year, $month,$divisionID,$connection){
      if($month <= 0)  {
         $month = 12;
         $year -= 1;
      }
      $sql = "SELECT `ytd_import_unit`, `ytd_sales_unit`, `ytd_system_loss`, `ytd_bill_amount`, `YTD_SALES_RATE`, `ytd_coll_amount`, `ytd_bc_ratio`, `ytd_ci_ratio`, demand.MAX_LOAD
               FROM desco.`PUBLISHED_MOD`, ";
      if($month >6)
         $sql .= "(SELECT MAX(MAX_LOAD) MAX_LOAD FROM desco.MAXIMUM_LOAD_DEMAND WHERE year = $year AND month >6 AND division_id = $divisionID) AS demand";            
      else 
         $sql .= "(SELECT MAX(MAX_LOAD) MAX_LOAD FROM desco.MAXIMUM_LOAD_DEMAND WHERE division_id = $divisionID AND (year = $year OR (YEAR = ($year-1) AND month >6))) AS demand"; 
               
      $sql .= " WHERE year = $year AND month=$month AND division_id = $divisionID";
      
      $resultSet = $connection -> query($sql);
      return $resultSet;
   }

   $mysqli->close();
?>