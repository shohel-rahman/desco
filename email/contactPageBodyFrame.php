<?php
	require ('employeelist.php');	
?>
	<div class="backmain">
		<div class="roundt">
			<div class="roundb">
				<div class="roundl">
					<div class="roundr">
						<div class="roundbl">
							<div class="roundbr">
								<div class="roundtl">
									<div class="roundtr">
										<div id="main">
											<span style="font-family: Vrinda;"></span>
											<div class="title_bg">
												<em>Email ID and Contact No <br/><br/></em>
											</div>																						
											<form id = 'searchForm' method = 'get'>
												<fieldset>
													<legend>Search Employee List</legend>													
													<select name="designationSelector" id="designationSelector" onChange = 'searchByDesgination()'>
														<option <?php echo $searchDesignation=="All"?'selected':'';?> value = 'All'>All Designation</option>
														<option <?php echo $searchDesignation=="Managing Director"?'selected':'';?> value='Managing Director'>Managing Director</option>
														<option <?php echo $searchDesignation=="Executive Director"?'selected':'';?> value='Executive Director'>Executive Director</option>
														<option <?php echo $searchDesignation=="Chief Engineer"?'selected':'';?> value='Chief Engineer'>Chief Engineer</option>
														<option <?php echo $searchDesignation=="General Manager"?'selected':'';?> value='General Manager'>General Manager</option>
														<option <?php echo $searchDesignation=="Superintending Engineer"?'selected':'';?> value='Superintending Engineer'>Superintending Engineer</option>
														<option <?php echo $searchDesignation=="Deputy General Manager"?'selected':'';?> value='Deputy General Manager'>Deputy General Manager</option>
														<option <?php echo $searchDesignation=="Executive Engineer"?'selected':'';?> value='Executive Engineer'>Executive Engineer</option>
														<option <?php echo $searchDesignation=="Manager"?'selected':'';?> value='Manager'>Manager</option>
														<option <?php echo $searchDesignation=="Sub-Divisional Engineer"?'selected':'';?> value='Sub-Divisional Engineer'>Sub-Divisional Engineer</option>
														<option <?php echo $searchDesignation=="Deputy Manager"?'selected':'';?> value='Deputy Manager'>Deputy Manager</option>
														<option <?php echo $searchDesignation=="Assistant Engineer"?'selected':'';?> value='Assistant Engineer'>Assistant Engineer</option>
														<option <?php echo $searchDesignation=="Assistant Manager"?'selected':'';?> value='Assistant Manager'>Assistant Manager</option>														
													</select>
													<label for="name">Name:</label>
													<input  value = '<?php echo $searchName ?>' type="text" id="name" name="name" placeholder = 'Emplyoee Name' 
														onkeypress="return (event.charCode == 45)||(event.charCode > 64 && event.charCode < 91) || 
														(event.charCode > 96 && event.charCode < 123)"
													>												
													<input type="submit"  value="Search" id='searchBtn' name="searchBtn">
													<button id= 'resetBtn' onClick= 'resetForm()'>Reset</button>
												</fieldset>
											</form>
											<?php
												if($numrows < 1 ){
													echo '<br/><h5 style="color:red; text-align:center;">Sorry, No Employee Found by this Name. Please Type the Employee Name Correctly</h5><br/><br/>';
												}
											?>									
											<table id='customers1' style = 'width: 650px;'>
												<tr>
													<td colspan = 5></td>(Not According to Seniority)
												</tr>
												<tr>
													<th><span style='font-size:16px'>Name</span></th>
													<th><span style='font-size:16px'>Designation</span></th>
													<th><span style='font-size:16px'>Email Address</span></th>
													<th><span style='font-size:16px'>Office</span></th>
													<th><span style='font-size:16px'>Contact</span></th>
												</tr>
												<?php echo $dataTable; ?>
											</table>
											<br/>
											<table style='align:center'>
												<tr>
													<td> <?php echo $paginationTable; ?> </td>
												</tr>
											</table>
											<br/><br/>
											<div class="title_bg"><em>For any update</em></div>
											<div><code>ই-মেইল করতে পারেনঃ <a href="mailto:emran@desco.org.bd">emran@desco.org.bd</a></code></div>
											<br/><br/><br/>
											<div class="clear"></div>        
            				</div>
              			<br/>
										<div class="clear"></div>        
              		</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

<style>
	fieldset{
		margin-bottom: 0.3em;
	}	
	legend{
		font-family: "Arial";
		font-weight: 750;
		padding: 0.3em;
	}
	select{
		padding: 0.01em;
		margin: 0.2em 0.4em ;
	}
	label{
		font-weight: 600;
	}
	input{
		padding: 0.1em;
		margin: 0.2em 0.4em;
	}
	#resetBtn{
		margin: 0.4em;
	}
	#customers1{
		font-family:"Trebuchet MS", Arial, Helvetica, sans-serif;
		width:630px;
		border-collapse:collapse;
	}
	#customers1 td, #customers1 th {
		font-size:0.7em;
		border:1px solid #66CC00;
		padding:3px 7px 2px 7px;
	}
	#customers1 th{
		font-size:0.9em;
		text-align:left;
		padding-top:5px;
		padding-bottom:4px;
		background-color:#A7C942;
		color:#fff;
	}
	#customers1 tr.alt td{
		font-size:0.7em;
		color:#000;
		background-color:#A7C942;
	}
</style>
<script>
	function toggleSearchBtn(){
		if(document.getElementById('name').value.length != 0){
			document.getElementById('searchBtn').disabled = false;
		}
		else document.getElementById('searchBtn').disabled = true;
	}
	function searchByDesgination(){
		document.getElementById('searchForm').submit();				
	}
	function resetForm(){		
		document.getElementById('name').value = '';		
		document.getElementById('designationSelector').selectedIndex = 0;
		window.location.href = '/bangla/contact.php';
	}
</script>