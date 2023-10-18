<?php
    if(!empty($_GET['year'])){
        $year = $_GET['year'];
        $month = $_GET['month'];
     }
     else{
        $yearSql = "SELECT MAX(year) as 'year' FROM desco.`PUBLISHED_TRF_WISE_MOD`";   
        $resultYear = $mysqli -> query($yearSql);
        $year = $resultYear ->  fetch_assoc()['year'];
  
        $monthSql = "SELECT MAX(month) as 'month' FROM desco.`PUBLISHED_TRF_WISE_MOD` WHERE `year` = $year";
        $resultMonth = $mysqli -> query($monthSql);   
        $month = $resultMonth -> fetch_assoc()['month'];
     }
   $division = !empty($_GET['division']) ? $_GET['division'] : "DESCO";
?>
<h3 class="text-center">Monthly Tariff-wise Summary of <?php echo($division); echo ($division !="DESCO" ? " S & D Division" : ""); ?></h3>
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
            <th scope="col" rowspan="2">Tariff</th>
            <th scope="col" colspan="3">No. of Consumers</th>
            <th scope="col" colspan="3">Sales Unit (kWh)</th>
            <th scope="col" colspan="3">Billed Amount (Tk)</th>
            <th scope="col" colspan="3">Collection Amount (Tk)</th>
            <th scope="col" rowspan="2">Collection / Bill Ratio (%)</th>
            <!-- <th scope="col" rowspan="2">Account Receivables (MTk)</th> -->
         </tr>
         <tr>
            <th scope="col">Prepaid</th>
            <th scope="col">Postpaid</th>
            <th scope="col">Total</th>
            <th scope="col">Prepaid</th>
            <th scope="col">Postpaid</th>
            <th scope="col">Total</th>
            <th scope="col">Prepaid</th>
            <th scope="col">Postpaid</th>
            <th scope="col">Total</th>
            <th scope="col">Prepaid</th>
            <th scope="col">Postpaid</th>
            <th scope="col">Total</th>
         </tr>
      </thead>
      <tbody>
      <?php 
         $result = tariffDataPull($year, $month, $division,$mysqli);        
         foreach($result as $dataRow){
            $printRow = 
                "<tr>
                    <th scope='row'>" . $dataRow['tariff'] . "</th>
                    <td>" . $dataRow['pre_consumer_no'] . "</td>
                    <td>" . $dataRow['post_consumer_no'] . "</td>
                    <td>" . $dataRow['total_consumer_no'] . "</td>
                    <td>" . number_format($dataRow['pre_sales_unit']) . "</td>
                    <td>" . number_format($dataRow['post_sales_unit']) . "</td>
                    <td>" . number_format($dataRow['total_sales_unit']) . "</td>
                    <td>" . number_format($dataRow['pre_bill_amount']) . "</td>
                    <td>" . number_format($dataRow['post_bill_amount']) . "</td>
                    <td>" . number_format($dataRow['total_bill_amount']) . "</td>
                    <td>" . number_format($dataRow['pre_collection_amount']) . "</td>
                    <td>" . number_format($dataRow['post_collection_amount']) . "</td>
                    <td>" . number_format($dataRow['total_collection_amount']) . "</td>                    
                    <td>" . number_format($dataRow['bc_ratio'],2,".",",") . "</td>                
                </tr>";
            echo $printRow;
         }
         $result = totalDataPull($year, $month, $division,$mysqli) -> fetch_array(MYSQLI_ASSOC);
         echo "<tr>
            <th scope='row'>Total</th>
            <th>" . $result['pre_con'] . "</th>
            <th>" . $result['post_con'] . "</th>
            <th>" . $result['tot_con'] . "</th>
            <th>" . number_format($result['pre_unit']) . "</th>
            <th>" . number_format($result['post_unit']) . "</th>
            <th>" . number_format($result['tot_unit']) . "</th>
            <th>" . number_format($result['pre_bill']) . "</th>
            <th>" . number_format($result['post_bill']) . "</t>
            <th>" . number_format($result['tot_bill']) . "</th>
            <th>" . number_format($result['pre_coll']) . "</th>
            <th>" . number_format($result['post_coll']) . "</th>
            <th>" . number_format($result['tot_coll']) . "</th>                    
            <th>" . number_format($result['tot_bc'],2,".",",") . "</th>  
         </tr>";
      ?>
      </tbody>
      <tfoot></tfoot>
   </table>
</div>

<?php
   function tariffDataPull($year, $month,$division,$connection){
      if($month <= 0) $month = 12;

      $sql = "SELECT a.tariff, IFNULL(b.consumer_nos,0) AS 'pre_consumer_no', IFNULL(a.consumer_nos - IFNULL(b.consumer_nos,0),0) AS 'post_consumer_no', 
               a.consumer_nos AS 'total_consumer_no', IFNULL(b.UNIT_KWH,0) AS 'pre_sales_unit',  IFNULL(a.unit_kwh - IFNULL(b.UNIT_KWH,0),0) AS 'post_sales_unit',
               a.unit_kwh AS 'total_sales_unit', IFNULL(b.NET_BILL,0) AS 'pre_bill_amount', IFNULL(a.net_bill - IFNULL(b.NET_BILL,0),0) AS 'post_bill_amount', 
               a.net_bill AS 'total_bill_amount', IFNULL(b.NET_COLLECTION,0) AS 'pre_collection_amount', IFNULL(a.net_collection - IFNULL(b.NET_COLLECTION,0),0) AS 'post_collection_amount',
               a.net_collection AS 'total_collection_amount', (a.net_collection/a.net_bill)*100 AS 'bc_ratio' FROM desco.`PUBLISHED_TRF_WISE_MOD` a LEFT OUTER JOIN desco.MOD_PREPAID_DATA b 
               ON a.division_id = b.DIVISION_id AND a.year = b.YEAR AND a.month = b.MONTH AND a.tariff = b.TARIFF
               WHERE a.tariff IN('A','B','C1','C2','D1','D2','D3','E','T','MF3','MT1','MT2','MT3','MT4','MT5','MT6','MT7','MT8','HT1','HT2','HT3','HT4','EHT1')";
      
      $sql .= " AND a.year = $year AND a.month = $month AND a.division_id = ".findDivIdByName($division, $connection);
                
         
         // $sql = "SELECT A.year, A.month, A.tariff, IFNULL(B.pre_con,0) AS 'pre_consumer_no', A.tot_con - IFNULL(B.Pre_Con,0) AS 'post_consumer_no', A.tot_con AS 'total_consumer_no',
         // IFNULL(B.pre_unit,0) AS 'pre_sales_unit', A.tot_unit - IFNULL(B.pre_unit,0) AS 'post_sales_unit', A.tot_unit AS 'total_sales_unit',
         // IFNULL(B.pre_bill,0) AS 'pre_bill_amount', A.tot_bill - IFNULL(B.pre_bill,0) AS 'post_bill_amount', A.tot_bill AS 'total_bill_amount',
         // IFNULL(B.pre_coll,0) AS 'pre_collection_amount', A.tot_coll - IFNULL(B.pre_coll,0) AS 'post_collection_amount', A.tot_coll AS 'total_colllection_amount',
         // (A.tot_coll/A.tot_bill)*100 AS 'bc_ratio'
         
         // FROM 
         // (SELECT year, month, tariff, SUM(consumer_no) AS 'tot_con', SUM(sales_unit) AS 'tot_unit', SUM(bill_amount) AS 'tot_bill', SUM(col_amount) AS 'tot_coll' 
         // FROM mod_trf_wise_data_2 WHERE tariff IN('A','B','C1','C2','D1','D2','D3','E','T','MT1','MT2','MT3','MT4','MT5','MT6','MT7','MT8','HT1','HT2','HT3','HT4','EHT1') 
         // AND year = $year AND month = $month GROUP BY tariff ORDER BY tariff) A 
         
         // LEFT OUTER JOIN      
         
         // (SELECT year, month, tariff, SUM(consumer_nos) AS 'pre_con', SUM(unit_kwh) AS 'pre_unit', SUM(net_bill) AS 'pre_bill', SUM(net_collection) AS 'pre_coll'
         // FROM MOD_PREPAID_DATA WHERE year = $year AND month = $month GROUP BY tariff ORDER BY tariff) B ON A.year = B.year AND A.month = B.month AND A.tariff = B.tariff";
     
      $resultSet = $connection -> query($sql);
      return $resultSet;
   }
   function totalDataPull($year, $month,$division,$connection){
      $sql = "SELECT SUM(pre.CONSUMER_NOS) as pre_con, tot.consumer - sum(pre.consumer_nos) as post_con, tot.consumer as tot_con, SUM(pre.UNIT_KWH) as pre_unit,
               tot.sales_unit - SUM(pre.UNIT_KWH) as post_unit, tot.sales_unit as tot_unit, SUM(pre.NET_BILL) as pre_bill,tot.bill_amount - SUM(pre.NET_BILL) as post_bill,
               tot.bill_amount as tot_bill, SUM(pre.NET_COLLECTION) as pre_coll, tot.collection_amount - SUM(pre.NET_COLLECTION) as post_coll, tot.collection_amount as tot_coll,
               tot.bc_ratio as tot_bc
               FROM desco.PUBLISHED_MOD tot, desco.MOD_PREPAID_DATA pre
               WHERE pre.YEAR = $year AND pre.MONTH =$month AND tot.year =$year and tot.month =$month and 
               pre.DIVISION_ID = ".findDivIdByName($division, $connection)  ." AND tot.division_id = ".findDivIdByName($division, $connection);
      
      $resultSet = $connection -> query($sql);
      return $resultSet;
   }
?>