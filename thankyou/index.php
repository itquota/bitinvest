
<?php

parse_str($_SERVER['QUERY_STRING']);

$status=$_GET['status'];
?>

<!DOCTYPE html>
<html lang="en">
  <head>
  <link href="img/favicon.ico" rel="icon">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
   
    <meta name="description" content="">
    <meta name="author" content="">
    
    <title>Payment Status</title>

    
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet" />
  

   
   
    
  </head>

    <body id="bg_img">
  
  
        <section id="">
            <div class="container">
              <div class="row  bg_top">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="thumb_circle center-block">
                   <div class="thumb_pos">
                       <img src="img/gb_logo.png" class="img-responsive">
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="container">
              <div class="row  bg_bottom">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="center-block ryt_img">
                    <?php if($status =='success'){?>
                        <img src="img/sign-check-icon.png" class="img-responsive center-block">
                        <div class="thank_you text-center text-capitalize">Thank You!</div>
                        <p class="text-center" style="font-size:20px;">Your payment was processed <span style="color:#23b67f;">successfully</span></p>
                
                    <?php } else if($status =='timedOut'){?>
                        <img src="img/timeout.png" class="thumb_circle img-responsive center-block" style="margin-top:0px!important;">
                        <div class="thank_you text-center text-capitalize">Timed out!</div>
                        <p class="text-center" style="font-size:20px;">Do not pay to the address of this invoice. Return back to the merchant to start a new payment.</p>                    
                    <?php } else {?>
        <!--                <img src="img/timeout.png" class="thumb_circle img-responsive center-block" style="margin-top:0px!important;"> -->
                        <div class="thank_you text-center text-capitalize">Something Went Wrong !</div>
                        <p class="text-center" style="font-size:20px;">Payment Failed.Return back to the merchant to start a new payment.</p>                      
                    
                    <?php }?>

                 
                  </div>
                </div>
              </div>
            </div>
       </section>
  
    </body>
  </html>