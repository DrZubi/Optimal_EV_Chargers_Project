<!DOCTYPE HTML>
<html>
    <head>
         <title>NiH Solutions</title>
         <meta charset="utf-8">
         <meta name="viewport" content="width=device-width, initial-scale=1">
         <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
         <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
         <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
         <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet" type="text/css">
         <link href="css/style.css" type="text/css" rel="stylesheet" />
         <link href="css/lightbox.css" rel="stylesheet">
         <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet">
         <script src="js/main.js"></script>
         <script src="js/lightbox.js"></script>

    </head>
    <body id="myPage" data-spy="scroll" data-target=".navbar" data-offset="60">

        
        <?php
            session_start();
            $_SESSION['InputAccepted'] = '0';
        ?>
            <div class="parallax" id="bodyContent"> 

                <nav class="navbar navbar-inverse">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span> 
                        </button>
                        <a class="navbar-brand" href="index.html"> NiH Solutions </a>
                    </div>
                    <div class="collapse navbar-collapse" id="myNavbar">
                    <ul class="nav navbar-nav">
                        <li><a href="https://web.ics.purdue.edu/~g1114005/index.html#about">ABOUT</a></li>
                        <li><a href="https://web.ics.purdue.edu/~g1114005/index.html#work">WORK</a></li>
                        <li><a href="https://web.ics.purdue.edu/~g1114005/index.html#contact">CONTACT US</a></li>
                        <li><a href="https://web.ics.purdue.edu/~g1114005/home.php">HELLO <?php echo $_SESSION['username']?></a></li>
                    </ul>
                    </div>
                </nav>
                   </nav>

                    <header class="container-fluid">   
            <div class="text">
                <h1> Welcome Back! </h1>
                <br>
                <h3> Please fill out the following details and submit a .csv file with the starting and ending latitudes and longitudes formatted as follows, without any headings</h3>
                
                <br>
                <center><table border=3>
                    <tr><th>Starting Longitude</th>
                        <th>Starting Latitude</th>
                        <th>Ending Longitude</th>
                        <th>Ending Latitude</th>
                    </tr>
                </table></center>
                <br>
            </header>

            <form name="UserInput" method="post" enctype='multipart/form-data'>
                <div class="col-md-2 mb-8 col-centered">
                <div class="form-group">
                   <label for="Region">Geographical Region</label>
                   <select required type="text" class="form-control" id="Region" name="Region" placeholder="Geographical Region">
                       <option>Midwest</option>
                       <option>West</option>
                       <option>North West</option>
                       <option>South</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="Budget">Budget(USD)</label>
                    <input required type="text" class="form-control" id="Budget" name="Budget" placeholder="10000">
                </div>
                    <div class="form-group">
                    <label for="SLong">Please upload the .csv file here: <br>(Please make sure it is .csv and not UTF-8)</label>
                    <input required type="file" class="form-control-file" id="UserInput" name="UserInput">
                    </div>           
                    <center><input type="submit" value="Submit" class="btn" name="Submit" id="SignUpSubmit"></center>
                </div>

    </form>
            

            <?php
            session_start();
            $servername = "mydb.itap.purdue.edu";
            $username = "g1114005";
            $password = "Cornfields2021";
            $dbname = "g1114005";
            
            $conn = mysqli_connect($servername, $username, $password, $dbname);

            /*if (!$conn) {
                die("Connection failed: " . mysqli_connect_error());
            }
            else{
            echo "Connected successfully<br>";
            }*/

            $sql1 = "SELECT CustomerID FROM Customers WHERE Username = '".$_SESSION['username']."'" ;
            $result = mysqli_query($conn,$sql1);
            if(mysqli_num_rows($result)> 0){
                $row = mysqli_fetch_assoc($result);
                $_SESSION['CId'] = $row["CustomerID"];
                $CId = $_SESSION['CId'];
            }
            /*else {
               echo "Error: " . $sql . "<br>" . $conn->error;
                echo "CustomerId not found" ;
            }*/

            function test_input($data) {
				$data = trim($data);
				$data = stripslashes($data);
				$data = htmlspecialchars($data);
				return $data;
            }

            if(isset($_POST['Submit'])){

                $Region = test_input($_POST["Region"]);
                $Budget = test_input($_POST["Budget"]);

                $sql = "INSERT INTO Invests(CustomerID) VALUES ('$CId')" ;
                if($conn->query($sql) === TRUE)
                {
                    $sql2 = "SELECT InvestID from Invests WHERE CustomerID ='".$CId. "' ORDER BY CreationTime DESC LIMIT 1 ;";
                    $result = mysqli_query($conn,$sql2);
                    if(mysqli_num_rows($result)> 0){

                        $row = mysqli_fetch_assoc($result);
                        $Invests = $row["InvestID"];
                        $_SESSION['Invests'] = $Invests;
                        //echo $Invests;
                        if($_FILES['UserInput']['name']){
                        
                        $arrFileName = explode('.',$_FILES['UserInput']['name']);
                        if($arrFileName[1] == 'csv'){
                        $handle = fopen($_FILES['UserInput']['tmp_name'], "r");
                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        
                            $item1 = mysqli_real_escape_string($conn,$data[0]);
                            $item2 = mysqli_real_escape_string($conn,$data[1]);
                            $item3 = mysqli_real_escape_string($conn,$data[2]);
                            $item4 = mysqli_real_escape_string($conn,$data[3]);

                            $import="INSERT into Edges(CustomerID,InvestID,Start_long, Start_lat, End_long, End_lat, Geographic_Region, Budget) values('$CId','$Invests','$item1','$item2','$item3','$item4', '$Region','$Budget')";
                            
                            if ($conn->query($import) === TRUE) {
                                $_SESSION['InputAccepted'] = '1' ;
                              // echo $import."Input Accepted";
                            } 
                            else{
                                echo  $import ."Error in importing data".$conn->error ;
                                $_SESSION['InputAccepted'] = '0' ;
                            }
                        }
                        fclose($handle);
                        print "<center>Your data was successfully imported!</center>";
                        }
                        }
                        }
                    }
                    else{
                        echo "Error: " . $sql2 . "<br>" . $conn->error;
                    }
                }
            else{
                //echo"Error";
            }
            mysqli_close($conn);
            ?>
            <br>
            <br>
            <br>
            <center>
            <form name="ViewOutput" method="post" action=COutput.php>
            <label for="Submit">Click below to view the proposed network<br></label>
            <br>
            <input type="submit" value="Let's Go!" class="btn" name="Submit" id="SignUpSubmit"></center>
            </form>

    </div>
    
    </body>

</html>