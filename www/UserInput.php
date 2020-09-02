<html>
	<body>
        <?php
            session_start();
            $servername = "mydb.itap.purdue.edu";
            $username = "g1114005";
            $password = "Cornfields2021";
            $dbname = "g1114005";
            
            $conn = mysqli_connect($servername, $username, $password, $dbname);

            // Check connection
            /*if (!$conn) {
                die("Connection failed: " . mysqli_connect_error());
            }
            else{
            echo "Connected successfully<br>";
            }
            */
            $Region = $Budget = $csvname = "" ;
			
			if($_SERVER["REQUEST_METHOD"] == "POST"){
				$Region = test_input($_POST["Region"]);
                $Budget = test_input($_POST["Budget"]);
                $csvname = test_input($_POST["UserInput"]);
                $target_dir = "uploads/";
                $target_file = $target_dir . basename($_FILES["UserInput"]["name"]);
                $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
                echo $target_file;
                $uploadOk = 1;
                if($imageFileType != "csv" ) {
                        $uploadOk = 0;
                    }
            }
            
			function test_input($data) {
				$data = trim($data);
				$data = stripslashes($data);
				$data = htmlspecialchars($data);
				return $data;
            }
			
            $sql1 = "SELECT CustomerID FROM Customers WHERE Username = '".$_SESSION['username']."'" ;
            $result = mysqli_query($conn,$sql1);
            if(mysqli_num_rows($result)> 0){
                $row = mysqli_fetch_assoc($result);
                $_SESSION['CId'] = $row["CustomerID"];
                $CId = $_SESSION['CId'];
                echo $CId;
            }
            else {
               echo "Error: " . $sql . "<br>" . $conn->error;
                echo "CustomerId not found" ;
            }


            if ($uploadOk != 0) {
                if (move_uploaded_file($_FILES["importfile"]["tmp_name"], $target_dir.'importfile.csv')) {
          
                  // Checking file exists or not
                  $target_file = 'importfile.csv';
                  $fileexists = 0;
                  if (file_exists($target_file)) {
                     $fileexists = 1;
                  }
                  if ($fileexists == 1 ) {
          
                     // Reading file
                     $file = fopen($target_file,"r");
                     $i = 0;
          
                     $importData_arr = array();
                                 
                     while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
                       $num = count($data);
          
                       for ($c=0; $c < $num; $c++) {
                          $importData_arr[$i][] = $data[$c];
                       }
                       $i++;
                     }
                     fclose($file);

                     $skip = 0;
           // insert import data
           foreach($importData_arr as $data){
              if($skip != 0){
                 $SLat = $data[0];
                 $SLong = $data[1];
                 $ELat = $data[2];
                 $ELong = $data[3];

                 // Checking duplicate entry
                 
                    // Insert record
                    $insert_query = "INSERT into Edges(CustomerID, Start_Long, Start_Lat, End_Long, End_Lat) values('$CId','$SLat','$SLong','$ELat','$ELong')";
                    mysqli_query($con,$insert_query);
                 }
              
              $skip ++;
           }
           $newtargetfile = $target_file;
           if (file_exists($newtargetfile)) {
              unlink($newtargetfile);
           }
         }
        }
    }
          


            /*

            echo $csvname;
            $ext=substr($csvname,strrpos($csvname,"."),(strlen($csvname)-strrpos($csvname,".")));
             echo $ext;
             echo "<br>";
            
            //we check,file must be have csv extention
            if($ext==".csv")
            {
              $file = fopen($csvname, "r");
                     while (($emapData = fgetcsv($file, 4, ",")) !== FALSE)
                     {
                        $sql = "INSERT into Edges(CustomerID, Start_Long, Start_Lat, End_Long, End_Lat) values('$CId','$emapData[0]','$emapData[1]','$emapData[2]')";
                        mysqli_query($con, $sql);
                     }
                     fclose($file);
                     echo "CSV File has been successfully Imported.";
            }
            else {
                echo "Error: Please Upload only CSV File";
            }
            */
             

            //$sql2 = "INSERT INTO Edge(CustomerID, Start_Long, Start_Lat, End_Long, End_Lat) VALUES('$CId','$SLong','$SLat', '$ELong', '$ELat')" ;
           /* if ($conn->query($query) === TRUE) {
                $_SESSION['InputAccepted'] = '0' ;
                echo "
                Input Accepted <script>setTimeout(\"location.href = 'https://web.ics.purdue.edu/~g1114005/home.php';\");</script>";
            } else {
                echo "Error: " . $sql2 . "<br>" . $conn->error;
                $_SESSION['InputAccepted'] = '0' ;
                echo "<br>Input error";
                echo "<script>setTimeout(\"location.href = 'https://web.ics.purdue.edu/~g1114005/home.php#';\",10000);</script>";
            }
*/

            mysqli_close($conn);
		?>
	</body>
</html>
			