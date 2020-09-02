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
            if (!$conn) {
                die("Connection failed: " . mysqli_connect_error());
            }
            else{
            echo "Connected successfully<br>";
            }

            $Email = $Password="" ;
			
			if($_SERVER["REQUEST_METHOD"] == "POST"){
				$Email = test_input($_POST["Id"]);
				$Password = test_input($_POST["pwd"]);
            }
            
			function test_input($data) {
				$data = trim($data);
				$data = stripslashes($data);
				$data = htmlspecialchars($data);
				return $data;
			}

			$sql = "SELECT Username, CustomerType FROM Customers WHERE Email = '".$Email."' AND Password = '".$Password."'" ;
            $result = mysqli_query($conn,$sql);
            if(mysqli_num_rows($result)> 0){
                $row = mysqli_fetch_assoc($result);
                echo $row['CustomerType'];
                if($row['CustomerType']=='Client')
                {
                    $_SESSION['username'] = $row["Username"];
                    echo "Login successful!  <br> Redirecting...";
                    echo "<script>setTimeout(\"location.href = 'https://web.ics.purdue.edu/~g1114005/Chome.php';\",3000);</script>";  
                }
                elseif($row['CustomerType']=='User')
                {
                    $_SESSION['username'] = $row["Username"];
                    echo "Login successful!  <br> Redirecting...";
                    echo "<script>setTimeout(\"location.href = 'https://web.ics.purdue.edu/~g1114005/Uhome.php';\",3000);</script>";  
                }
            }
            else {
                echo "Error: " . $sql . "<br>" . $conn->error;
                echo "Email Id or Password is incorrect. Please try again. <br> Redirecting...";
               // echo "<script>setTimeout(\"location.href = 'https://web.ics.purdue.edu/~g1114005/index.html#';\",5000);</script>";
            }


            mysqli_close($conn);
		?>
	</body>
</html>
			