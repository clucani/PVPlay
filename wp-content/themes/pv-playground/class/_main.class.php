<?php

class emuPV extends emuApp
{
    function config()
    {
        $this->emuAppID = 'emuPV';
        $this->menuName = 'PV';
        $this->dbPrefix = 'emu_dry_';
        // $this->forceInstall = true;
    }

    function init()
    {
        $this->loadManager('theme');
    }

    public function getPhases($plots, $threshold, $excluded)
    {
      require_once $this->pluginPath.'/lib/PHPExcel/Classes/PHPExcel.php';
      
      $x_vals = array();
      $y_vals = array();

      $stored_slope = array();
      $stored_intercept = array();
      $stored_steyx = array();

      // Our calculated values
      $slope = '';
      $intercept = '';
      $steyx = '';
      $diff_with_previous = '';

      // To record our different phases
      $phases = array();
      
      // Easier to find the correct phases if we calculate the plot values
      // in reverse.
      // $plots = array_reverse($plots);

      // Start with the first phase...
      $current_phase = 0;

      // $plots = array_reverse($plots);

      // Loop through the points in each sequence
      foreach($plots as $plot)
      {
        $x = (float) $plot[0];
        $y = (float) $plot[1];

        $exclude_current_points = false;

        // Do we need to exclude these points
        foreach($excluded as $exclude)
        {
          $exclude_points = explode(',', $exclude);
          
          if( $x == (float) $exclude_points[0] && $y == (float) $exclude_points[1] )
            $exclude_current_points = true;
        }

        if($exclude_current_points)
        {
          // We'll still add the points to the members list...
          $phases[$current_phase]["members"][] = (object) array(
              'x' => $x, 
              'y' => $y, 
              'steyx' => '-', 
              'steyxDiff' => '-',
              'slope' => '-',
              'intercept' => '-',
              'excluded' => true
          );

          // but nothing else...
          continue;
        }

        $x_vals[] = $x;
        $y_vals[] = $y;
      
        // ... we can only calculate the slope and intercept when have at least 2 points  
        if( count($x_vals) >= 2 )
        {
          $stored_slope[] = $slope = PHPExcel_Calculation_Statistical::SLOPE($y_vals, $x_vals);
          $stored_intercept[] = $intercept = PHPExcel_Calculation_Statistical::INTERCEPT($y_vals, $x_vals);
        }
        
        // And we can only do the std error of the regression calculation when
        // we have at least three points
        if( count($x_vals) >= 3 )
        {
          $stored_steyx[] = $steyx = PHPExcel_Calculation_Statistical::STEYX($y_vals, $x_vals);
        }
        else
        {
          $steyx = 0;
          $diff_with_previous = 0;
        }
        
        // Do we have a new phase?

        // Well, we can only check for a new phase if we have at least two steyx values to compare...
        if(count($stored_steyx) >= 2)
        {
          // Get the current stored array position
          $current_array_position = count($stored_steyx) - 1; // -1 because array indexes start at 0

          // Compare the current steyx with the previous...
          $diff_with_previous = $stored_steyx[$current_array_position] / $stored_steyx[$current_array_position - 1];
          
          // ... and if it is more than our threshold then ...
          if( $diff_with_previous > (float) $threshold ) 
          {
            // ... we're (probably) into a new phase.
            $current_phase++;
            
            // Start with a new set of points and steyx vals (add the current set of points and steyx values as the initial 
            // values for the new phase)
            $x_vals = array(array_pop($x_vals));
            $y_vals = array(array_pop($y_vals));
            $stored_steyx = array(array_pop($stored_steyx));

            // Because we can't get a slope or intercept from one set of points
            // we'll use the last calculated slope and intercept values (which
            // grouped the first new phase points into the set of last phase points)
            // i.e. we won't do this here:
            // $slope = ''; $intercept = '';
          }
        
        }

        // Update the current phase values
        $phases[$current_phase]["members"][] = (object) array(
            'x' => $x, 
            'y' => $y, 
            'steyx' => $steyx, 
            'steyxDiff' => $diff_with_previous,
            'slope' => $slope,
            'intercept' => $intercept,
            'excluded' => false
        );

        $phases[$current_phase]["slope"] = $slope; // Replace the last calculated slope
        $phases[$current_phase]["intercept"] = $intercept; // Replace the last calculated intercept
        $phases[$current_phase]["number"] = $current_phase;

      }
    
      return $phases;
    }    


    function loadCoreStyles()
    {
        if( !is_admin() )
        {
            wp_enqueue_style('bootstrap', $this->sThemeURL.'/css/bootstrap.css');
            wp_enqueue_style('bootstrap-theme', $this->sThemeURL.'/css/bootstrap-theme.min.css', array('bootstrap'));
            wp_enqueue_style('styles', $this->sThemeURL.'/style.css', array('bootstrap-theme'));
        }
    }


}


?>