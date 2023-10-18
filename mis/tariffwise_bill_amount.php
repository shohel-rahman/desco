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
<h3 class="text-center">Tariff-wise Bill Amount(MTk) of <?php echo($division); echo ($division !="DESCO" ? " S & D Division" : ""); ?></h3>
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
            <th scope="col">Month-Year</th>
            <th scope="col">A</th>
            <th scope="col">B</th>
            <th scope="col">C1</th>
            <th scope="col">C2</th>
            <th scope="col">D1</th>
            <th scope="col">D2</th>
            <th scope="col">D3</th>
            <th scope="col">E</th>
            <th scope="col">EHT1</th>
            <th scope="col">HT1</th>
            <th scope="col">HT2</th>
            <th scope="col">HT3</th>
            <th scope="col">HT4</th>
            <th scope="col">MF3</th>
            <th scope="col">MT1</th>
            <th scope="col">MT2</th>
            <th scope="col">MT3</th>
            <th scope="col">MT4</th>
            <th scope="col">MT5</th>
            <th scope="col">MT6</th>
            <th scope="col">MT7</th>
            <th scope="col">MT8</th>
            <th scope="col">T</th>            
         </tr>
      </thead>
      <tbody>
      <?php      
         $resultSet = getTariffwiseBill($year, $month, $division,$mysqli);         
         $numRows = $resultSet->num_rows;
         $numCols = $resultSet->field_count;     
         while($result = $resultSet->fetch_array(MYSQLI_NUM)){            
            $printRow = "<tr><td>". date('M', mktime(0, 0, 0, $result[1], 10)) . "-" .substr($result[0],2,2). "</td>";
            for($i = 2; $i<$numCols; $i++){               
               $printRow .= "<td>" . number_format($result[$i],2,".",",") . "</td>";               
            }
            $printRow .= "</tr>";
            echo $printRow;
         }
         echo "<br/><br/>";         
      ?>
      </tbody>
      <tfoot></tfoot>
   </table>
</div>

<?php 
function getTariffwiseBill($year, $month,$division,$connection){
   if($month <= 0) $month = 12;
   
   $sql ="SELECT year,month,
         (SUM(CASE WHEN tariff IN ('A', 'A1', 'A2', 'A3', 'A4')  THEN net_bill/1000000 ELSE 0 END)) A,
         (SUM(CASE WHEN tariff IN ('B') THEN net_bill/1000000 ELSE 0 END)) B,
         (SUM(CASE WHEN tariff IN ('C1') THEN net_bill/1000000 ELSE 0 END)) C1,
         (SUM(CASE WHEN tariff IN ('C2') THEN net_bill/1000000 ELSE 0 END)) C2,         
         (SUM(CASE WHEN tariff IN ('D1') THEN net_bill/1000000 ELSE 0 END)) D1,
         (SUM(CASE WHEN tariff IN ('D2') THEN net_bill/1000000 ELSE 0 END)) D2,
         (SUM(CASE WHEN tariff IN ('D3') THEN net_bill/1000000 ELSE 0 END)) D3,
         (SUM(CASE WHEN tariff IN ('E', 'E.', 'E') THEN net_bill/1000000 ELSE 0 END)) E,
         (SUM(CASE WHEN tariff IN ('EHT1') THEN net_bill/1000000 ELSE 0 END)) EHT1,
         (SUM(CASE WHEN tariff IN ('HT1') THEN net_bill/1000000 ELSE 0 END)) HT1,
         (SUM(CASE WHEN tariff IN ('HT2') THEN net_bill/1000000 ELSE 0 END)) HT2,
         (SUM(CASE WHEN tariff IN ('HT3') THEN net_bill/1000000 ELSE 0 END)) HT3,
         (SUM(CASE WHEN tariff IN ('HT4') THEN net_bill/1000000 ELSE 0 END)) HT4,
         (SUM(CASE WHEN tariff IN ('MF3') THEN net_bill/1000000 ELSE 0 END)) MF3,
         (SUM(CASE WHEN tariff IN ('MT1') THEN net_bill/1000000 ELSE 0 END)) MT1,
         (SUM(CASE WHEN tariff IN ('MT2') THEN net_bill/1000000 ELSE 0 END)) MT2,
         (SUM(CASE WHEN tariff IN ('MT3') THEN net_bill/1000000 ELSE 0 END)) MT3,
         (SUM(CASE WHEN tariff IN ('MT4') THEN net_bill/1000000 ELSE 0 END)) MT4,
         (SUM(CASE WHEN tariff IN ('MT5') THEN net_bill/1000000 ELSE 0 END)) MT5,
         (SUM(CASE WHEN tariff IN ('MT6') THEN net_bill/1000000 ELSE 0 END)) MT6,
         (SUM(CASE WHEN tariff IN ('MT7') THEN net_bill/1000000 ELSE 0 END)) MT7,
         (SUM(CASE WHEN tariff IN ('MT8') THEN net_bill/1000000 ELSE 0 END)) MT8,
         (SUM(CASE WHEN tariff IN ('T') THEN net_bill/1000000 ELSE 0 END)) T
         FROM desco.`PUBLISHED_TRF_WISE_MOD` 
         WHERE `DIVISION_ID` = ". findDivIdByName($division, $connection). " AND (year = $year OR year = $year-1 OR (year = $year-2 AND month >6))
            GROUP BY year, month ORDER BY year, month";
    $resultSet = $connection -> query($sql);
    return $resultSet;
   }
?>