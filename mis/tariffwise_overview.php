<?php
    if(!empty($_GET['year'])){
        $year = $_GET['year'];
        $month = $_GET['month'];
     }
     else{
        $yearSql = "SELECT MAX(year) as 'year' FROM `mod_trf_wise_data_2`";   
        $resultYear = $mysqli -> query($yearSql);
        $year = $resultYear ->  fetch_assoc()['year'];
  
        $monthSql = "SELECT MAX(month) as 'month' FROM `mod_trf_wise_data_2` WHERE `year` = $year";
        $resultMonth = $mysqli -> query($monthSql);   
        $month = $resultMonth -> fetch_assoc()['month'];
     }
   $division = !empty($_GET['division']) ? $_GET['division'] : "DESCO";
?>
<h3 class="text-center">Tariff-wise Overview of <?php echo($division); echo ($division !="DESCO" ? " S & D Division" : ""); ?></h3>
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
                    <td>" . $dataRow['pre_sales_unit'] . "</td>
                    <td>" . $dataRow['post_sales_unit'] . "</td>
                    <td>" . $dataRow['total_sales_unit'] . "</td>
                    <td>" . number_format($dataRow['pre_bill_amount']) . "</td>
                    <td>" . number_format($dataRow['post_bill_amount']) . "</td>
                    <td>" . number_format($dataRow['total_bill_amount']) . "</td>
                    <td>" . number_format($dataRow['pre_collection_amount']) . "</td>
                    <td>" . number_format($dataRow['post_collection_amount']) . "</td>
                    <td>" . number_format($dataRow['total_colllection_amount']) . "</td>                    
                    <td>" . number_format($dataRow['C/B Ratio'],2,".",",") . "</td>                
                </tr>";
            echo $printRow;
         }
      ?>
      </tbody>
      <tfoot></tfoot>
   </table>
</div>

<?php 
function tariffDataPull($year, $month,$division,$connection){
   if($month <= 0) $month = 12;

   if($division != "DESCO" && $division != "Desco" && $division != "desco"){ 
         $sql = "SELECT a.division, a.year, a.month, a.tariff, IFNULL(b.consumer_nos,0) AS 'pre_consumer_no', IFNULL(a.consumer_no - IFNULL(b.consumer_nos,0),0) AS 'post_consumer_no',
         a.consumer_no AS 'total_consumer_no', IFNULL(b.UNIT_KWH,0) AS 'pre_sales_unit', IFNULL(a.sales_unit - IFNULL(b.UNIT_KWH,0),0) AS 'post_sales_unit', 
         a.sales_unit AS 'total_sales_unit',IFNULL(b.NET_BILL,0) AS 'pre_bill_amount', IFNULL(a.bill_amount - IFNULL(b.NET_BILL,0),0) AS 'post_bill_amount',
          a.bill_amount AS 'total_bill_amount', IFNULL(b.NET_COLLECTION,0) AS 'pre_collection_amount',
         IFNULL(a.col_amount - IFNULL(b.NET_COLLECTION,0),0) AS 'post_collection_amount', a.col_amount AS 'total_colllection_amount',
         (a.col_amount/a.bill_amount)*100 AS 'C/B Ratio' FROM `mod_trf_wise_data_2` a LEFT OUTER JOIN MOD_PREPAID_DATA b ON a.division = b.DIVISION
         AND a.year = b.YEAR AND a.month = b.MONTH AND a.tariff = b.TARIFF
         WHERE a.tariff IN('A','B','C1','C2','D1','D2','D3','E','T','MT1','MT2','MT3','MT4','MT5','MT6','MT7','MT8','HT1','HT2','HT3','HT4','EHT1')
         AND a.division = '$division' AND a.year = $year AND a.month = $month";      
   }
   else{
      $sql = "SELECT A.year, A.month, A.tariff, IFNULL(B.pre_con,0) AS 'pre_consumer_no', A.tot_con - IFNULL(B.pre_con,0) AS 'post_consumer_no', A.tot_con AS 'total_consumer_no',
      IFNULL(B.pre_unit,0) AS 'pre_sales_unit', A.tot_unit - IFNULL(B.pre_unit,0) AS 'post_sales_unit', A.tot_unit AS 'total_sales_unit',
      IFNULL(B.pre_bill,0) AS 'pre_bill_amount', A.tot_bill - IFNULL(B.pre_bill,0) AS 'post_bill_amount', A.tot_bill AS 'total_bill_amount',
      IFNULL(B.pre_coll,0) AS 'pre_collection_amount', A.tot_coll - IFNULL(B.pre_coll,0) AS 'post_collection_amount', A.tot_coll AS 'total_colllection_amount',
      (A.tot_coll/A.tot_bill)*100 AS 'C/B Ratio'
      
      FROM 
      (SELECT year, month, tariff, SUM(consumer_no) AS 'tot_con', SUM(sales_unit) AS 'tot_unit', SUM(bill_amount) AS 'tot_bill', SUM(col_amount) AS 'tot_coll' 
      FROM mod_trf_wise_data_2 WHERE tariff IN('A','B','C1','C2','D1','D2','D3','E','T','MT1','MT2','MT3','MT4','MT5','MT6','MT7','MT8','HT1','HT2','HT3','HT4','EHT1') 
      AND year = $year AND month = $month GROUP BY tariff ORDER BY tariff) A 
      
      LEFT OUTER JOIN      
      
      (SELECT year, month, tariff, SUM(consumer_nos) AS 'pre_con', SUM(unit_kwh) AS 'pre_unit', SUM(net_bill) AS 'pre_bill', SUM(net_collection) AS 'pre_coll'
      FROM MOD_PREPAID_DATA WHERE year = $year AND month = $month GROUP BY tariff ORDER BY tariff) B ON A.year = B.year AND A.month = B.month AND A.tariff = B.tariff";
   }
    $resultSet = $connection -> query($sql);
    return $resultSet;
}
?>