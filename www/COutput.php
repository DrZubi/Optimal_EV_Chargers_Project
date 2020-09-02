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
                <a class="navbar-brand" href="#"> NiH Solutions </a>
            </div>
            <div class="collapse navbar-collapse" id="myNavbar">
            <ul class="nav navbar-nav">
                <li><a href="https://web.ics.purdue.edu/~g1114005/index#about">ABOUT</a></li>
                <li><a href="https://web.ics.purdue.edu/~g1114005/index#work">WORK</a></li>
                <li><a href="https://web.ics.purdue.edu/~g1114005/index#contact">CONTACT US</a></li>
                <li> <a href="https://web.ics.purdue.edu/~g1114005/Chome.php"> HELLO <?php 
                 echo "  ".$_SESSION["username"] ;
                ?></a></li>
            </ul>
            </div>
        </nav>
        Click here to Logout
        <div class="parallax" id="bodyContent"> 
            <header class="container-fluid">   
            <div class="text">
                <h1> Your network is ready! </h1>
                <h3>Based on your information your details are as follows: </h3>
                <br>
                <h3>Region: <?php echo $_SESSION["region"];?></h3>
                <h3>Budget: $<?php echo $_SESSION["budget"];?></h3>
                <br>
                <h3> Please enter further details below to begin the calculations:</h3>
                </div>
         
            <center>
            <div id="map"></div>
</center>
            </script>
                <script async defer
                    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAdnZmsL5wOqec1dhIHpDD9Q6K-1bH1zKQ&callback=initMap">
                </script>
    <div class="parallax" id="bodyContent"> 
        <br><br>
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
        $Id = $Longitude = $Latitude = $Type = $Partnership = $StationID ="" ;

        echo $_SESSION['Invests'];
        $sql = "SELECT LocationID, Longitude, Latitude, ChargerType, PartnershipType, StationID FROM Locations WHERE InvestID = 13";
        $result = mysqli_query($conn,$sql);
        $rows = mysqli_num_rows($result) ;
        $locations = array();
        $count = 0;
       if(mysqli_num_rows($result)> 0){
                echo "<center><table border = 1><tr><th>LocationID</th><th>Longitude</th><th>Latitude</th><th>Charger Type</th><th>Partnership Type</th><th>Station ID</th></b></tr>";
                while($row = mysqli_fetch_assoc($result)){
                    echo "<tr><td>".$row["LocationID"]."</td><td>".$row["Longitude"]."</td><td>".$row["Latitude"]."</td><td>".$row["ChargerType"]."</td><td>".$row["PartnershipType"]."</td><td>".$row["StationID"]."</td></tr>";
                    array_push($locations,array($row["ChargerType"],$row["Latitude"],$row["Longitude"])) ;
                }
                echo "</table></center>";
                $_SESSION['locations'] = $locations;
            }
            else{
                echo "0 results";
            }

        mysqli_close($conn);


    ?>

    
<script type='text/javascript'>
        function initMap() {
  var center = {lat: 41.8781, lng: -87.6298};
  var locations= <?php echo json_encode($_SESSION['locations']);?>;
  
  var infowindow =  new google.maps.InfoWindow({});
  var map = new google.maps.Map(document.getElementById('map'), {
    zoom:4,
    center: center
  });

  var count, marker;

  for (count = 0; count < locations.length; count++) {
    marker = new google.maps.Marker({
      position: new google.maps.LatLng(locations[count][1], locations[count][2]),
      map: map,
      title: locations[count][0]
    });

    google.maps.event.addListener(marker, 'click', (function (marker, count) {
      return function () {
        infowindow.setContent(locations[count][0]);
        infowindow.open(map, marker);
      }
    })(marker, count));
  }
  

}
    </script>

  
    <br>
    <br>
    <br>
    <br>
    </div>
    <script type="text/javascript">
	/* resize the image(s) on page load */
	$(document).ready(function() {
		resize_all_parallax();
	});

	/* resize the image(s) on page resize */
	$(window).on('resize', function(){
		resize_all_parallax();
	});

	/* keep all of your resize function calls in one place so you don't have to edit them twice (on page load and resize) */
	function resize_all_parallax() {
		var div_id = 'bodyContent'; /* the ID of the div that you're resizing */
		var img_w = 1000; /* the width of your image, in pixels */
		var img_h = 864; /* the height of your image, in pixels */
		resize_parallax(div_id,img_w,img_h);
	}

	/* this resizes the parallax image down to an appropriate size for the viewport */
	function resize_parallax(div_id,img_w,img_h) {
		var div = $('#' + div_id);
		var divwidth = div.width();
		if (divwidth < 769) { var pct = (img_h/img_w) * 105; } /* show full image, plus a little padding, if on static mobile view */
		else { var pct = 60; } /* this is the HEIGHT as percentage of the current div WIDTH. you can change it to show more (or less) of your image */
		var newheight = Math.round(divwidth * (pct/100));
		newheight = newheight  + 'px';
		div.height(newheight);
	}
</script>
    </body>

</html>