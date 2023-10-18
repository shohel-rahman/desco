<!DOCTYPE html>
<html>
  <head>   
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">    
    <title>MOD | DESCO</title>
  </head>

	<?php
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);

		// include('orcl_con.php');
		// $_SESSION['user'] = "AEICT";  
		// $yearSql = "SELECT MAX(YEAR) AS YEAR FROM DESCO.MOD_SUMMARY_TOTAL";  
		// $resultYear = $conn -> Execute($yearSql);
		// $year = $resultYear->fields['YEAR'];
				
    // $monthSql = "SELECT MAX(MONTH) as MONTH FROM DESCO.MOD_SUMMARY_TOTAL WHERE YEAR = $year";
    // $resultMonth = $conn -> Execute($monthSql);   
    // $month = $resultMonth -> fields['MONTH'];		

		// $rowSql = "SELECT COUNT(*) AS NSDD FROM DESCO.MOD_SUMMARY_TOTAL where YEAR = $year AND MONTH = $month";
		// $resultRowCount = $conn -> Execute($rowSql);   
    // $sddNos = $resultRowCount -> fields['NSDD'];
		
		// include ('connection.php');
    // $connection_maria = new mysqli($DBHOST, $DBUSER,$DBPASS,$DBNAME);
				
    // if ($connection_maria -> connect_errno) {      
    //   echo "Failed to connect MySQL Server: " . $connection_maria -> connect_error;
    //   exit();
    // }		
	?>
	
	<body>
		<h3 class="text-center">Monthly Operational Data of <?php echo(date('F', mktime(0, 0, 0, $month, 10)) . "-" . $year); ?></h3>
		<h4 class="text-center">MOD Confirmed: <span class="badge bg-success"><?php echo($sddNos); ?></span>  S & D</h4>
		<h4 class="text-center">Yet to Confirm: <span class="badge bg-danger"> <?php echo(24 - $sddNos); ?></span>  S & D</h4>
		<div class="table-responsive mt-4 mx-3">
				<table class="table table-bordered border-secondary table-hover text-center align-middle">
						<thead>
							<tr>
								<th scope="col" colspan='7'><?php echo(date('F', mktime(0, 0, 0, $month, 10)) . "-" . $year); ?></th>
								<th scope="col" colspan='4'>Year to Date</th>
								<th scope="col" colspan='2'>Collection Adjusment</th>
							</tr>
							<tr>
								<th scope="col">Division</th>
								<th scope="col">Import Unit</th>
								<th scope="col">Audited Import</th>
								<th scope="col" >Actual Sales Unit</th>
								<th scope="col"style='width:2em'>Actual System Loss</th>
								<th scope="col">Sales Adjustment</th>									
								<th scope="col"style='width:2em'>Adjusted System Loss</th>
								<th scope="col">Sales Adjustment upto <?php echo(date('M/y', mktime(0, 0, 0, $month-1, 10, $year))); ?></th>
								<th scope="col" style='width:2em'>Actual System Loss</th>
								<th scope="col" style='width:2em'>Adjusted System Loss</th>
								<th scope="col">Sales Adjustment upto <?php echo(date('M/y', mktime(0, 0, 0, $month, 10, $year))); ?></th>
								<th scope="col">MOD Collection</th>
								<th scope="col">Collection Adjustment</th>
							</tr>
						</thead>
						<tbody>						
							<form id='modForm' method='POST' action='save_mod_collection.php'>
								<tr>
									<input class='form-control' name='year' hidden value=<?php echo $year; ?>>
									<input class='form-control' name='month' hidden value=<?php echo $month; ?>>
									<button type='submit' disabled class='d-none'>Prevent enter</button>
								</tr>
							<?php
								$result = modPullDESCO($year,$month,$conn);
								$pblsdYtdPrevMnth = ytdPblshdDataPull('DESCO',$year,$month-1,$connection_maria) -> fetch_array(MYSQLI_ASSOC);
								$ytdSlsAdjst = findYtdSalesAdjustment('DESCO', $year, $month-1, $connection_maria) -> fetch_array(MYSQLI_ASSOC);
								$descoRow =
										"<tr>
											<th scope='row'>" . $result->fields['DIVISION'] . "</th>
											<th>" . number_format($result->fields['IMPORT_UNIT'],0,".","") . "</th>
											<th><input type='number' step='1' id='' name='import9999' min='1' onChange='auditImportChng(this.parentElement.parentElement)'
												value='".number_format($result->fields['IMPORT_UNIT'],0,".","")."' class='form-control' style='width:7.5em'>
											</th>
											<th>" . number_format($result->fields['SALES_UNIT'],0,".","") . "</th>
											<th style='width:2em'>" . number_format($result->fields['SYSTEM_LOSS'],2,".","") . "</th>
											<th style='width:5em'><input type='number' step='1' onChange='adjustSales(this.parentElement.parentElement)' name='slsadjst9999' class='form-control'></th>											
											<th style='width:2em'><input type='number' step ='0.0000001' onChange='adjustSystemLoss(this.parentElement.parentElement)' name='syslos9999' class='form-control'
														value='".number_format($result->fields['SYSTEM_LOSS'],2,".","")."'>
											</th>
											<th class='d-none'>" . number_format($pblsdYtdPrevMnth['ytd_import_unit'],0,".","") . "</th>
											<th class='d-none'><input name='ytdimport9999' readonly type='number'
												value='" . number_format(($result->fields['IMPORT_UNIT'] + $pblsdYtdPrevMnth['ytd_import_unit']),0,".","") . "'>												
											</th>
											<th class='d-none'>" . number_format($pblsdYtdPrevMnth['ytd_sales_unit'],0,".","") . "</th>
											<th class='d-none'><input name='ytdsales9999' readonly type='number'
												value='" . number_format(($result->fields['SALES_UNIT'] + $pblsdYtdPrevMnth['ytd_sales_unit']),0,".","") . "'>												
											</th>
											<th><input name='prvmntytdslsadjst9999' readonly type='number' class='form-control'
												value='". number_format($ytdSlsAdjst['ytd_sales_adjustment'],0,".","") ."'>											
											</th>																		
											<th>" . number_format(calculateSystemLoss($result->fields['IMPORT_UNIT'] + $pblsdYtdPrevMnth['ytd_import_unit'],$result->fields['SALES_UNIT'] + $pblsdYtdPrevMnth['ytd_sales_unit']),2,".","") . "</th>
											<th style='width:2em'><input name='ytdsysloss9999' readonly type='number' class='form-control'
												value='" . number_format(calculateSystemLoss($result->fields['IMPORT_UNIT'] + $pblsdYtdPrevMnth['ytd_import_unit'],$result->fields['SALES_UNIT'] + $pblsdYtdPrevMnth['ytd_sales_unit']),2,".","") . "'>
											</th>
											<th>". number_format($ytdSlsAdjst['ytd_sales_adjustment'],0,".","") ."</th>
											<th>" . number_format($result->fields['COLLECTION_AMOUNT'],0,".","") . "</th>
											<th><input type='number' step='1' name='coladj9999' min='1' onChange='(this.parentElement.parentElement)'
														value='' class='form-control'>
											</th>
											<th class='d-none'><input name='prvmnthytdcoll9999' value='" . number_format($pblsdYtdPrevMnth['ytd_coll_amount'],0,".","") ."' type='number' readonly></th>
										</tr>";
								echo $descoRow;
								
								$result = modPullDivision($year,$month,$conn);						
								foreach($result as $dataRow){
									$pblsdYtdPrevMnth = ytdPblshdDataPull($dataRow['DIVISION'],$year,$month-1,$connection_maria) -> fetch_array(MYSQLI_ASSOC);
									$ytdSlsAdjst = findYtdSalesAdjustment($dataRow['DIVISION'], $year, $month-1, $connection_maria) -> fetch_array(MYSQLI_ASSOC);
									$divisionId = findDivisionIdByName($dataRow['DIVISION'], $connection_maria);									
									$divisionRow =
											"<tr>												
												<th scope='row'>" . $dataRow['DIVISION'] . "</th>
												<td>" . number_format($dataRow['IMPORT_UNIT'],0,".","") . "</td>
												<td><input class='form-control' name='import".$divisionId."'  style='width:7.5em' onChange='auditImportChng(this.parentElement.parentElement)'
													type='number' step='1' id='' min='1' value='".number_format($dataRow['IMPORT_UNIT'],0,".","")."'>
												</td>
												<td>" . number_format($dataRow['SALES_UNIT'],0,".","") . "</td>
												<td style='width:2em'>" . number_format($dataRow['SYSTEM_LOSS'],2,".","") . "</td>
												<td style='width:5em'><input class='form-control' type='number' step='1' id=''  name='slsadjst".$divisionId."'
													onChange='adjustSales(this.parentElement.parentElement)'>
												</td> 
												<td style='width:2em'><input name='syslos".$divisionId."' value='".number_format($dataRow['SYSTEM_LOSS'],2,".","")."' type='number' readonly class='form-control'></td>																					
												<td class='d-none'>" . number_format($pblsdYtdPrevMnth['ytd_import_unit'],0,".","") . "</td>
												<td class='d-none'><input name='ytdimport".$divisionId."' type='number' readonly
													value='" . number_format($dataRow['IMPORT_UNIT'] + $pblsdYtdPrevMnth['ytd_import_unit'],0,".","")."'>
												</td>
												<td class='d-none'>" . number_format($pblsdYtdPrevMnth['ytd_sales_unit'],0,".","") . "</td>												
												<td class='d-none'><input name='ytdsales".$divisionId."' type='number' readonly
													value='" . number_format($dataRow['SALES_UNIT'] + $pblsdYtdPrevMnth['ytd_sales_unit'],0,".","") ."'>
												</td>
												<td><input name='prvmntytdslsadjst".$divisionId."' type='number' readonly class='form-control'
													value='". number_format($ytdSlsAdjst['ytd_sales_adjustment'],0,".","") ."'>
												</td>											                    
												<td>" . number_format(calculateSystemLoss($dataRow['IMPORT_UNIT'] + $pblsdYtdPrevMnth['ytd_import_unit'],$dataRow['SALES_UNIT'] + $pblsdYtdPrevMnth['ytd_sales_unit']),2,".","") . "</td>    
												<td style='width:2em'><input name='ytdsysloss".$divisionId."' type='number' readonly class='form-control'
													value='". number_format(calculateSystemLoss($dataRow['IMPORT_UNIT'] + $pblsdYtdPrevMnth['ytd_import_unit'],$dataRow['SALES_UNIT'] + $pblsdYtdPrevMnth['ytd_sales_unit']),2,".","") ."'>
												</td>
												<td>". number_format($ytdSlsAdjst['ytd_sales_adjustment'],0,".","") ."</td>
												<td>" . number_format($dataRow['COLLECTION_AMOUNT'],0,".","") . "</td>
												<td><input class='form-control' name='coladj".$divisionId."'  onChange='(this.parentElement.parentElement)'
														type='number' step='1' min='1' value=''>
												</td>
												<td class='d-none'><input name='prvmnthytdcoll".$divisionId."' value='" . number_format($pblsdYtdPrevMnth['ytd_coll_amount'],0,".","") ."' type='number' readonly ></td>
											</tr>";
									echo $divisionRow;																
								}										
								$result = modPullDESCO($year,$month,$conn);	
								$descoRow =
											"<tr>
												<th>Total</th>
												<th>Import Unit</th>
												<th>Sales Unit</th>
												<th>Actual System Loss</th>
												<th>Sales Adjustment</th>
												<th>Adjusted System Loss</th>
												<th>Sales Adjustment upto ". date('M/y', mktime(0, 0, 0, $month, 10, $year)) ."</th>
												<th>YTD Adjusted System Loss</th>
												<th></th>
												<th>Confirmation</th>
												<th></th>
												<th>MOD Collection</th>
												<th>Collection Adjustment</th>
											</tr>
											<tr id='chkingRow'>
												<th scope='row'>" . $result->fields['DIVISION'] . "</th>												
												<th>Sum of SDDs import</th>												
												<th>" . number_format($result->fields['SALES_UNIT'],0,".","") . "</th>
												<th>" . number_format($result->fields['SYSTEM_LOSS'],2,".","") . "</th>
												<th></th>
												<th></th>
												<th></th>
												<th></th>																				                    
												<th></th>	
												<th><button type='submit' disabled>Save Data</button></th>
												<th></th>
												<th>".number_format($result->fields['COLLECTION_AMOUNT'],0,".","") ."</th>
												<th>total col adj</th>
											</tr>											
										</form>";
									echo $descoRow;							
								$conn -> close();												
							?>
						</tbody>
						<tfoot></tfoot>
				</table>
			</div>
	</body>
	<?php 

			function modPullDESCO($year,$month,$connection){
				$sql = "select 'DESCO' AS division, SUM(import_unit) AS import_unit,SUM(sales_unit) AS sales_unit,
								((SUM(import_unit)-SUM(sales_unit))/SUM(import_unit))*100 AS system_loss, SUM(COLLECTION_AMOUNT) AS COLLECTION_AMOUNT from DESCO.mod_summary_total where year = '$year' and month = '$month'";
				$resultSet = $connection ->Execute($sql);			 
				return $resultSet;
			}			

			function findYtdSalesAdjustment($division, $year, $month, $connection){
				$divisionId = findDivisionIdByName($division,$connection);	
				if($month <= 0){
					$month = 12;
					$year -= 1;
					$sql = "SELECT `ytd_sales_adjustment` FROM `ACTUAL_MOD` WHERE `year`= $year AND `month` = $month AND division_id = '$divisionId'";
				}
				else if($month == 6){
					$sql = "SELECT 0 AS `ytd_sales_adjustment` FROM `ACTUAL_MOD` WHERE `year`= $year AND `month` = $month AND division_id = '$divisionId'";
				}
				else{
					$sql = "SELECT `ytd_sales_adjustment` FROM `ACTUAL_MOD` WHERE `year`= $year AND `month` = $month AND division_id = '$divisionId'";
				}
				//echo $sql;
				$resultSet = $connection -> query($sql);
				return $resultSet;
			}

			function modPullDivision($year,$month,$connection){
				$sql = "SELECT division,import_unit,sales_unit,system_loss, COLLECTION_AMOUNT FROM DESCO.mod_summary_total WHERE year = $year AND month = $month ORDER BY division ASC";
				$resultSet = $connection ->Execute($sql);			 
				return $resultSet;
			}
				
			function ytdPblshdDataPull($division, $year, $month, $connection){
				$divisionId = findDivisionIdByName($division,$connection);
				if($month <= 0){
					$month = 12;
					$year -= 1;
					$sql = "SELECT ytd_import_unit, ytd_sales_unit, ytd_coll_amount FROM PUBLISHED_MOD WHERE `year`= $year AND `month` = $month AND division_id = '$divisionId'";
				}
				else if($month == 6){
					$sql = "SELECT 0 AS ytd_import_unit,  0 AS ytd_sales_unit, 0 AS ytd_coll_amount FROM PUBLISHED_MOD WHERE `year`= $year AND `month` = $month AND division_id = '$divisionId'";
				}
				else {
					$sql = "SELECT ytd_import_unit, ytd_sales_unit, ytd_coll_amount FROM PUBLISHED_MOD WHERE `year`= $year AND `month` = $month AND division_id = '$divisionId'";
				}				
				//echo $sql;
				$resultSet = $connection -> query($sql);
				return $resultSet;
			}

			function findDivisionIdByName($divisionName, $connection){
				$sql = "SELECT `DIVISION_ID` FROM `SALES_DISTRIBUTION_DIVISION` WHERE DIVISION_NAME = '$divisionName'";				
    		$resultSet = $connection -> query($sql);
				return $resultSet -> fetch_array(MYSQLI_ASSOC)['DIVISION_ID'];
			}

			function calculateSystemLoss($import,$sales){
				return (($import-$sales)/$import)*100;
			}
	?>
	<style>
		input{
			width: 150px;
		}		
	</style>
	<script src="adjust.js"></script>
</html>