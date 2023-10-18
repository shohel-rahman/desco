<!DOCTYPE html>
<html>
  <head>   
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">    
    <title>MOD Reports | DESCO</title>
  </head>
  <?php
    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);
    
    require('../../../../opt/config/connection.php');
  ?>

  <body>
        <form action="reports.php" method = 'get'>
            <fieldset class="well the-fieldset">      
                <div class="w-100 my-4 d-print-none">
                    <div class="row w-100 mx-4 px-4">
                        <div class="col">
                            <select class="form-select" aria-label="Select a Zone" required onchange="populateCircleSelector(this)">
                                <option selected disabled value="">Select Zone</option>
                                <option value="desco">DESCO</option>
                                <option value="north">North Zone</option>
                                <option value="central">Central Zone</option>
                                <option value="south">South Zone</option>                                
                            </select>                       
                        </div>
                        <div class="col">
                            <select class="form-select" aria-label="Select a Circle" name= "circle" id="circleSelector" required onchange="populateDivisionSelector(this)">
                                <option selected disabled value="">Select Circle</option>                               
                            </select>                       
                        </div>
                        <div class="col">
                            <select class="form-select" aria-label="Select Division" name= "division" id="divisionSelector" required>
                                <option selected disabled value="">Select S & D</option>                                
                            </select>
                        </div>
                        <div class="col">
                            <select class="form-select" aria-label="Select Year" name="year" id="yearSelector" required>
                                <option selected disabled value="">Select Year</option>
                                <option value="2023">2023</option> 
                                <option value="2022">2022</option>                                                               
                            </select>
                        </div>
                        <div class="col">
                            <select class="form-select" aria-label="Select Month" name="month" id="monthSelector" required>
                                <option selected disabled value="">Select Month</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>            
                        </div>                   
                        <div class="col">
                            <button type="submit" class="btn btn-primary">Show Reports</button>
                        </div>
                        <div class="col">
                            <button type="submit" class="btn btn-primary" onclick="location='http://local.desco.org.bd/mis/reports.php';">Clear Filter</button>
                        </div>                
                    </div>
                </div>
            </fieldset>
        </form>
        
        <?php
            echo '<br />';
            include ('monthly_operational_summary.php');
            // echo '<a href="http://desco.gov.bd/site/view/reports/%E0%A6%AE%E0%A6%BE%E0%A6%B8%E0%A6%BF%E0%A6%95%20%E0%A6%85%E0%A6%AA%E0%A6%BE%E0%A6%B0%E0%A7%87%E0%A6%B6%E0%A6%A8%E0%A6%BE%E0%A6%B2%20%E0%A6%A1%E0%A6%BE%E0%A6%9F%E0%A6%BE/-/'.strtolower(date('F', mktime(0, 0, 0, $month, 10))).$year.'pg_1.pdf" 
            // style="text-decoration:none; margin-left:3%; margin-top:0%;" target="_blank" >Click Here for the Signed Copy of Monthly Operational Data</a>';
            echo '<br /> <br /> <br />';
            echo "<div class='pagebreak'></div>";
            include ('monthly_tariffwise_summary.php');
            echo '<br /> <br /> <br />';
            echo "<div class='pagebreak'></div>";
            include ('operational_summary_stats.php');
            echo '<br /> <br /> <br />';
            echo "<div class='pagebreak'></div>";
            include ('tariffwise_consumers.php');
            echo '<br /> <br /> <br />';
            echo "<div class='pagebreak'></div>";
            include ('tariffwise_sales_unit.php');
            echo '<br /> <br /> <br />';
            echo "<div class='pagebreak'></div>";
            include ('tariffwise_bill_amount.php');
            echo '<br /> <br /> <br />';
            echo "<div class='pagebreak'></div>";
            include ('tariffwise_sales_rate.php');
            echo '<br /> <br /> <br />';
            echo "<div class='pagebreak'></div>";
            include ('tariffwise_collection_amount.php');     
            echo '<br /> <br /> <br />';
            echo "<div class='pagebreak'></div>";
            // include ('categorywiseOverview.php');         
            $mysqli->close();
        ?>  
    
        <!-- JavaScript Bundle with Popper -->
        <style>
            @media print {
                .pagebreak {
                    page-break-before: always;
                }
                @page{                
                    margin: 0;
                    size: A4 landscape;
                    margin-top: 40px;
                }
                body {
                    zoom: 80%;
                }
            }            
        </style>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
        <script>
            function populateCircleSelector(selectedZone){
                const north = ['Dakshinkhan Circle', 'Tongi Circle', 'Uttara Circle'];
                const central = ['Baridhara Circle', 'Gulshan Circle'];
                const south = ['Agargaon Circle', 'Pallabi Circle', 'Rupnagar Circle'];
                
                const trgtElmnt = document.getElementById('circleSelector');
                
                switch (selectedZone.value){
                    case 'north':
                        addCircle(north);                       
                        break;
                    case 'central': 
                        addCircle(central);                        
                        break;
                    case 'south':
                        addCircle(south);    
                        break;
                    case 'desco':
                        trgtElmnt.required = false;
                        trgtElmnt.parentElement.nextElementSibling.children[0].required = false;
                    default: break;
                }
                function addCircle(zoneName){
                    while(trgtElmnt.childElementCount>1){
                        trgtElmnt.removeChild(trgtElmnt.childNodes[2]);
                    }
                    for(let i =0; i<zoneName.length;i++){
                        let opt = document.createElement('option');
                        opt.value = zoneName[i];
                        opt.innerHTML = zoneName[i];
                        trgtElmnt.add(opt);
                    }
                }
            }

            function populateDivisionSelector(selectedCircle){                               
                const agargaonCircle = ['Agargaon', 'Kallyanpur', 'Monipur'];
                const baridharaCircle = ['Baridhara', 'Bashundhara', 'Khilkhet'];
                const dakshinkhanCircle = ['Dakshinkhan', 'Uttarkhan', 'Shah Kabir'];
                const gulshanCircle = ['Badda', 'Gulshan', 'Joarshahara'];    
                const pallabiCircle = ['Ibrahimpur', 'Kafrul', 'Pallabi'];
                const rupnagarCircle = ['Eastern Housing', 'Rupnagar', 'Shahali'];
                const tongiCircle = ['Tongi Central', 'Tongi East', 'Tongi West'];
                const uttaraCircle = ['Uttara East', 'Uttara West', 'Turag'];
            
                const trgtElmnt = document.getElementById('divisionSelector');
                
                switch (selectedCircle.value){
                    case 'Agargaon Circle':
                        addDivision(agargaonCircle);                       
                        break;
                    case 'Baridhara Circle': 
                        addDivision(baridharaCircle);                        
                        break;
                    case 'Dakshinkhan Circle':
                        addDivision(dakshinkhanCircle);    
                        break;
                    case 'Gulshan Circle':
                        addDivision(gulshanCircle);
                        break;
                     case 'Pallabi Circle':
                        addDivision(pallabiCircle);                       
                        break;
                    case 'Rupnagar Circle': 
                        addDivision(rupnagarCircle);                        
                        break;
                    case 'Tongi Circle':
                        addDivision(tongiCircle);    
                        break;
                    case 'Uttara Circle':
                        addDivision(uttaraCircle);
                        break;    
                    default: break;
                }               
            
                function addDivision(circleName){
                    while(trgtElmnt.childElementCount>1){
                        trgtElmnt.removeChild(trgtElmnt.childNodes[2]);
                    }
                    for(let i =0; i<circleName.length;i++){
                        let opt = document.createElement('option');
                        opt.value = circleName[i];
                        opt.innerHTML = circleName[i];
                        trgtElmnt.add(opt);
                    }
                }
            }
        </script>
  </body>
</html>