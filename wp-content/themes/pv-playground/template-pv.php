<?php
/*
Template Name: PV Plot
*/

global $emuPV;

?>

<!DOCTYPE html>
<html>
  <head>
    <title>PV</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      // Load the Visualization API and the piechart package.
      google.load('visualization', '1.0', {'packages':['corechart']});
    
    </script>

    <?php wp_head(); ?>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

  </head>
  <body>

    <div class="container">

      <div class="row">
        <div class="col-md-10 col-md-offset-1" id="pv">
          
          <?php echo $emuPV->getView('pv-plot') ?>

        </div>
      </div>

    </div>

    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="<?php echo $emuPV->sThemeURL.'/js/bootstrap.min.js'?>"></script>

    <?php wp_footer(); ?>

  </body>
</html>
