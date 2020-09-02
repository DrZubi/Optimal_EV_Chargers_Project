
$('.navbar').affix({offset: {top: 0} });

$(document).ready(function(){

  $(".navbar a, footer a[href='#myPage'], header a[href='#about']").on('click', function(event) {

  if (this.hash !== "") {

    event.preventDefault();

    var hash = this.hash;

    $('html, body').animate({
      scrollTop: $(hash).offset().top
    }, 900, function(){

      window.location.hash = hash;
      });
    } 
  });
})

$(document).ready(function(){
    $(".navbar").affix();
});


window.onload = function() {
    var $recaptcha = document.querySelector('#g-recaptcha-response');

    if($recaptcha) {
        $recaptcha.setAttribute("required", "required");
    }
  }


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