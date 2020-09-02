
$(document).ready(function() {
  //change the integers below to match the height of your upper div, which I called
  //banner.  Just add a 1 to the last number.  console.log($(window).scrollTop())
  //to figure out what the scroll position is when exactly you want to fix the nav
  //bar or div or whatever.  I stuck in the console.log for you.  Just remove when
  //you know the position.
  $(window).scroll(function () { 

    if ($(window).scrollTop() >= 0) {
      $('#nav_bar').addClass('navbar-fixed-top');
    }
/*
    if ($(window).scrollTop() < 1) {
      $('#nav_bar').removeClass('navbar-fixed-top');
    }*/
  });
})

/*$("#contactForm").submit(function(event){
    event.preventDefault();
    submitForm();
});

function submitForm(){
    
    var name= $(#name).val();
    var email= $(#email).val();
    var comment= $(#comments).val();

    $.ajax({
      type: "POST",
      url: "../php/form.php",
      data: "Name: " + name + " Email: "+email+" Comment: "+comment,
        success: function (text){
          if(text=='success'){
            formSuccess();
          }
        }
    });
}

function formSuccess(text){
  $("#msgSubmit").removeClass("hidden");
}*/

function sendDetails() {
    var valid;	
    valid = validate();
    if(valid) {
        jQuery.ajax({
            url: "form-process.php",
            data:'Name='+$("#name").val()+'&Email='+
            $("email").val()+'&comment='+
            $("comment").val(),
            type: "POST",
            success:function(data){
                $("#mail-status").html(data);
            },
            error:function (){}
        });
    }
}

function initMap() {
  var center = {lat: 41.8781, lng: -87.6298};
  var uluru = {lat: -25.344, lng: 131.036};
  var map = new google.maps.Map(document.getElementById('map'), {
    zoom: 2,
    center: uluru
  });
  
  var marker = new google.maps.Marker({
    position: uluru,
    map: map
  });

}


function validate() {
    var valid = true;	
    $(".demoInputBox").css('background-color','');
    $(".info").html('');
    if(!$("#name").val()) {
        $("#name").html("(required)");
        $("#name").css('background-color','#FFFFDF');
        valid = false;
    }
    if(!$("#email").val()) {
        $("#email-info").html("(required)");
        $("#email").css('background-color','#FFFFDF');
        valid = false;
    }
    if(!$("#email").val().match(/^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/)) {
        $("#email-info").html("(invalid)");
        $("#email").css('background-color','#FFFFDF');
        valid = false;
    }
    if(!$("#comment").val()) {
        $("#comment-info").html("(required)");
        $("#comment").css('background-color','#FFFFDF');
        valid = false;
    }
    return valid;
}

$(window).scroll(function() {
  $(".slideanim").each(function(){
    var pos = $(this).offset().top;

    var winTop = $(window).scrollTop();
    if (pos < winTop + 600) {
      $(this).addClass("slide");
    }
  });
});