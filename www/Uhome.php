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
                        <a class="navbar-brand" href="https://web.ics.purdue.edu/~g1114005/index.html"> NiH Solutions </a>
                    </div>
                    <div class="collapse navbar-collapse" id="myNavbar">
                    <ul class="nav navbar-nav">
                        <li><a href="https://web.ics.purdue.edu/~g1114005/index.html#about">ABOUT</a></li>
                        <li><a href="https://web.ics.purdue.edu/~g1114005/index.html#work">WORK</a></li>
                        <li><a href="https://web.ics.purdue.edu/~g1114005/index.html#contact">CONTACT US</a></li>
                        <li><a href="https://web.ics.purdue.edu/~g1114005/Uhome.php">HELLO <?php echo $_SESSION['username']?></a></li>
                    </ul>
                    </div>
                </nav>
                   </nav>

                    <header class="container-fluid">   
            <div class="text">
                <h1> Welcome Back <?php echo $_SESSION['username']?>! </h1>
                <br>
                <h3> Let's find the closest charger to you:</h3>
                <br>
            </header>

            <form name="UserInput" method="post" action="UOutput.php">
                <div class="col-md-2 mb-8 col-centered">

                <div class="form-group">
                    <label for="Budget">Starting Address</label>
                    <input type="text" class="form-control" id="Addresst" name="Address" placeholder="Enter your address">
                </div>

                <div class="form-group">
                   <label for="Region">Stop preference</label>
                   <select type="text" class="form-control" id="StopP" name="StopP" default="Hotel">
                       <option>Hotel</option>
                       <option>Convenience Store</option>
                    </select>
                </div>          
                <br>
                    <center><input type="submit" value="Search" class="btn" name="Submit" id="SignUpSubmit"></center>
                </div>

</form>
           

    </div>
    
    </body>

</html>