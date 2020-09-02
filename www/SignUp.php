<html>
	<body>
        <?php
        
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
            $Email = $Password = $Username = $Region = $Budget ="" ;
			
			if($_SERVER["REQUEST_METHOD"] == "POST"){
				$Email = test_input($_POST["EmailId"]);
                $Password = test_input($_POST["Password"]);
                $Username = test_input($_POST["Username"]);
                $Type = test_input($_POST["AcType"]);
            }
            
			function test_input($data) {
				$data = trim($data);
				$data = stripslashes($data);
				$data = htmlspecialchars($data);
				return $data;
            }
            if($Type === 'Charger Supplier'){
                $Type = 1 ;
            }
            else{
                $Type = 2;
            }
			$sql = "INSERT INTO Customers(Email,Password,Username,CustomerType) VALUES('$Email','$Password', '$Username', '$Type')" ;
            
            if ($conn->query($sql) === TRUE) {
                echo "Account created successfully! <br> Redirecting to Login...";
                echo "<script>setTimeout(\"location.href = 'https://web.ics.purdue.edu/~g1114005/index.html#';\",3000);</script>";
            } else {
                echo $conn->error;
                echo "<br>There is already an account associated with this email id or username, please login or try again with different details";
                echo "<script>setTimeout(\"location.href = 'https://web.ics.purdue.edu/~g1114005/index.html#';\",3500);</script>";
            }


            mysqli_close($conn);
		?>
	</body>
</html>
			