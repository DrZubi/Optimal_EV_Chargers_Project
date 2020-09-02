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
         <link href="font-awesome/css/font-awesome.css" rel="stylesheet">
         <script src="js/main.js"></script>
         <script src="js/lightbox.js"></script>
    </head>
    <body id="home" data-spy="scroll" data-target= ".navbar" data-offset="60">
    <div class="parallax" id="bodyContent"> 
        <?php
            session_start();
            $_SESSION['InputAccepted'] = '0';
        ?>
        
        <nav class="navbar navbar-inverse">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span> 
                </button>
                <a class="navbar-brand" href="https://web.ics.purdue.edu/~g1114005/index.html"> NiH Solutions </a>
            </div>
            <div class="collapse navbar-collapse" id="myNavbar">
            <ul class="nav navbar-nav">
                <li><a href="https://web.ics.purdue.edu/~g1114005/index.html#about">ABOUT</a></li>
                <li><a href="https://web.ics.purdue.edu/~g1114005/index.html#work">WORK</a></li>
                <li><a href="https://web.ics.purdue.edu/~g1114005/index.html#contact">CONTACT US</a></li>
                <li> <a href="https://web.ics.purdue.edu/~g1114005/home.php"> HELLO <?php 
                 echo "  ".$_SESSION["username"] ;
                ?></a></li>
            </ul>
            <ul class="navbar-nav navbar-right">
           
            </ul>
            </div>
        </nav>
        <div class="parallax" id="bodyContent"> 
            <header class="container-fluid">   
            <div class="text">
                <h1> Your route is ready! </h1>
                
    <?php
          $servername = "mydb.itap.purdue.edu";
          $username = "g1114005";
          $password = "Cornfields2021";
          $dbname = "g1114005";
  
          $conn = mysqli_connect($servername, $username, $password, $dbname);
        
        if($_SERVER["REQUEST_METHOD"] == "POST"){
            $Address = test_input($_POST["Address"]);
            $Preference = test_input($_POST["StopP"]);
        }

        function test_input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }

        $import="INSERT into Address(Address,Preference) values('$Address','$Preference')";
                            
        if ($conn->query($import) === TRUE) {
            //echo $import."Input Accepted";
            }
        
        $sql = "SELECT charger_long,charger_lat,given_longitude,given_latitude FROM Route ";
        $result = mysqli_query($conn,$sql);
        $start = array();
        $end = array();
        if(mysqli_num_rows($result)> 0){
            while($row = mysqli_fetch_assoc($result)){
                $_SESSION['start_lt'] = $row['given_latitude'];
                $_SESSION['start_lg'] = $row['given_longitude'];
                $_SESSION['end_lt'] = $row['charger_lat'];
                $_SESSION['end_lg'] =$row['charger_long'];
            }
            
        
        }

        
        $_SESSION['Address'] = $Address ;
        $_SESSION['Preference'] = $Preference;
       /*$output = shell_exec("Rscript ./FinalRoutingFinal.R $Address $Preference");
        echo $output ; 
        */

        mysqli_close($conn);
    ?>

    <h3>Current Address: <?php echo $_SESSION["Address"];?></h3>
    <h3>Preference: <?php echo $_SESSION["Preference"];?></h3>
    <h3>Based on your address and stop preference, your closest charger is shown on the map as location B: </h3>
    <br>
    </div>
            
            <center>
            <div id="map"></div>
</center>
            </script>
                <script async defer
                    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAdnZmsL5wOqec1dhIHpDD9Q6K-1bH1zKQ&callback=initMap">
                </script>

    
<script type='text/javascript'>
     function initMap() {
  var directionsService = new google.maps.DirectionsService();
  var directionsRenderer = new google.maps.DirectionsRenderer();
  var chicago = new google.maps.LatLng(41.850033, -87.6500523);
  var mapOptions = {
    zoom:7,
    center: chicago
  }
  var map = new google.maps.Map(document.getElementById('map'), mapOptions);
  directionsRenderer.setMap(map);
  var start = {lat:<?php echo $_SESSION['start_lt']?>, lng:<?php echo $_SESSION['start_lg']?>};
  var end = {lat:<?php echo $_SESSION['end_lt']?>,lng:<?php echo $_SESSION['end_lg']?>};
  var request = {
    origin: start,
    destination: end,
    travelMode: 'DRIVING'
  };
  directionsService.route(request, function(result, status) {
    if (status == 'OK') {
      directionsRenderer.setDirections(result);
    }
  });
}
    </script>

</div>
</div>

</body>
</html>