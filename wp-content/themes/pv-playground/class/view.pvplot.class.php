<?php
class emuV_PvPlot extends emuView
{
    public $tolerance;
    public $visCounter = 1;
    public $phases;
    public $reversePoints = false;
    public $swapAxis = false;
    public $xVals;
    public $yVals;

    public function init()
    {
      $this->tolerance = post_val('tolerance', 2);
      $this->formSubmitted = post_val('plot') || post_val('update') || request_val('remote_vars');
      $this->reversePoints = post_val('reverse_points') ? true : false;
      $this->swapAxis = post_val('swap_axis') ? true : false;

      if(!$this->formSubmitted)
        return;

      $this->xVals = request_val('x_values');
      $this->yVals = request_val('y_values');
      $excluded = post_val('excluded', array());

      if(stripos($this->xVals, ','))
      {
        $x_vals = explode(',', $this->xVals);
      }
      else
      {
        // Can we split by line?
        $x_vals = explode("\n", $this->xVals);
      }

      if(stripos($this->yVals, ','))
      {
        $y_vals = explode(',', $this->yVals);
      }
      else
      {
        // Can we split by line?
        $y_vals = explode("\n", $this->yVals);
      }
      
      $points = array();

      for($i = 0, $i_count = count($x_vals); $i < $i_count; $i++)
      {
          if($this->swapAxis)
            $points[] = array((float)$y_vals[$i], (float)$x_vals[$i]);
          else
            $points[] = array((float)$x_vals[$i], (float)$y_vals[$i]);
      }

      if($this->reversePoints)
        $points = array_reverse($points);

      $this->phases = $this->emuApp->getPhases($points, $this->tolerance, $excluded);

    }

    public function build()
    {
      ?>
      <form method="post" action="/">
      <?php 
      if($this->formSubmitted)
      {
        ?>
        <div class="row">
          <div class="col-sm-6">
            <?php $this->plotForm()?>
            <?php $this->phasePlot() ?>
            <?php $this->phaseIntercepts() ?>
          </div>
          <div class="col-sm-6">
            <?php $this->phaseRegressions() ?>
            <?php $this->phasePoints() ?>
          </div>
        </div>
        <?php
      }
      else
      {
        ?>
        <div class="row">
          <div class="col-sm-6 col-sm-offset-3">
            <?php $this->plotForm()?>
            <input type="button" class="btn btn-sm btn-default pull-right" name="btn-test-data" id="btn-test-data" value="Fill with Test Data" />
          </div>
        </div>
        <?php
      }
      ?>
      </form>
      <?php
    }

    public function plotForm()
    {
      ?>
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="row">
            <div class="form-group col-sm-6">
              <label>X values</label>
              <textarea name="x_values" class="form-control" id="x_values" rows="5"><?php echo $this->xVals?></textarea>
              <span class="help-block">e.g. RWC</span>
            </div>
            <div class="form-group col-sm-6">
              <label>Y values</label>
              <textarea name="y_values" class="form-control" id="y_values" rows="5"><?php echo $this->yVals?></textarea>
              <span class="help-block">e.g. &Psi;</span>
            </div>
          </div>
          <div class="form-group">
            <label>Tolerance</label>
            <div class="form-inline">
              <input type="text" class="form-control" id="tolerance" name="tolerance" value="<?php echo $this->tolerance?>">
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="reverse_points" value="yes" <?php echo $this->reversePoints ? ' checked="checked"' : ''?>> Reverse points
                </label>
              </div>
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="swap_axis" value="yes" <?php echo $this->swapAxis ? ' checked="checked"' : ''?>> Swap axis
                </label>
              </div>
            </div>
          </div>
<!--           <div class="pull-right form-inline point-options">
            <div class="form-group">
                <div class="col-sm-3">
                </div>
            </div>
          </div>
 -->          
          <input type="submit" class="btn btn-primary" name="plot" value="Plot" />
        </div>
      </div>
      <?php
    }

    public function phasePlot()
    {
      ?>
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="phase-plot-wrapper">
            <div class="phase-plot" id="phasePlot"></div>
          </div>
        </div>
      </div>
      <script type="text/javascript">
      var resizeTimerID;

      function drawPhasePlot() {
        
        var data = google.visualization.arrayToDataTable([
          ['', '', { role: 'style' }],
          <?php
          foreach($this->phases as $phase)
          {
            $phase = (object) $phase;
            $phase_color = $this->getPhaseColor($phase->number);

            foreach($phase->members as $member)
            {
              ?>
              [ <?php echo $member->x?>, <?php echo $member->y?>, '<?php echo $member->excluded ? '#ccc' : $phase_color?>'],
              <?php            
            }
          }
          ?>
        ]);

        var options = {
          chartArea: {width: '90%', height: '90%'},
          legend: "none",
          // titlePosition: 'in', 
          axisTitlesPosition: 'in',
          hAxis: {textPosition: 'in', minValue: 0, viewWindowMode: 'pretty'}, 
          vAxis: {textPosition: 'in', minValue: 0, viewWindowMode: 'pretty'}
        };

        var chart = new google.visualization.ScatterChart(document.getElementById('phasePlot'));
        chart.draw(data, options);
      }


      jQuery(window).resize(function() {
          clearTimeout(resizeTimerID);
          resizeTimerID = setTimeout(drawPhasePlot, 500);
      });

      google.setOnLoadCallback(drawPhasePlot);
      
      </script>

      <?php
      $this->visCounter++;

    }

    public function phaseIntercepts()
    {
      ?>
      <?php

      // Now build the table to list the intercerpts
      $table = '<tr><td>Phase</td>';

      // Top row
      foreach($this->phases as $phase)
        $table .= '<td style="color: '.$this->getPhaseColor($phase["number"]).'">'.($phase["number"]+1).'</td>';

      $table .= '</tr>';

      // Rest of table
      foreach($this->phases as $phaseA)
      {
        $phaseA = (object) $phaseA;
          
        $table .= '<tr><td style="color: '.$this->getPhaseColor($phaseA->number).'">'.($phaseA->number+1).'</td>';

        foreach($this->phases as $phaseB)
        {
          $phaseB = (object) $phaseB;

          if($phaseB->number == $phaseA->number)
            $table .= '<td class="text-muted">'.round($phaseB->intercept, 3).'</td>';
          else
            $table .= '<td>'.round($this->calcPhasesIntercept($phaseA, $phaseB), 3).'</td>';
          
        }

        $table .= '</tr>';
      }

      ?>
      <table class="table">
        <thead>
          <tr>
            <th colspan="<?php echo count($this->phases)+1?>">Intercepts</th>
          </tr>
        </thead>
        <tbody>
          <?php echo $table?>
        </tbody>
      </table>
      <?php
    }

    public function calcPhasesIntercept($phaseA, $phaseB)
    {
        $x = ($phaseA->intercept - $phaseB->intercept) / ($phaseB->slope - $phaseA->slope); 
        return $phaseA->slope * $x + $phaseA->intercept;
    }

    public function phasePoints()
    {
      ?>
      <table class="table" id="tbl-points">
      <thead>
        <tr>
          <th colspan="8">Points</th>
        </tr>
        <tr>
          <td><em>x</em></td>
          <td><em>y</em></td>
          <td>Intercept</td>
          <td>Slope</td>
          <td>&sigma;</td>
          <td>&sigma;<sup>n</sup> / &sigma;<sup>n-1</sup></td>
          <td class="text-center">Exclude?</td>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach($this->phases as $phase)
        {
          $phase = (object) $phase;
          $phase_color = $this->getPhaseColor($phase->number);
      
          foreach($phase->members as $member)
          {
            $exclude_ref = $member->x.','.$member->y;

            ?>
            <tr<?php echo $member->excluded ? ' class="active"' : ''?>>
              <?php
              if($member->excluded)
              {
                ?>
                <td style="color: #ccc"><?php echo $member->x?></td>
                <td style="color: #ccc"><?php echo $member->y?></td>
                <td style="color: #ccc">-</td>
                <td style="color: #ccc">-</td>
                <td style="color: #ccc">-</td>
                <td style="color: #ccc">-</td>
                <?php
              }
              else
              {
                ?>
                <td style="color: <?php echo $phase_color?>"><?php echo $member->x?></td>
                <td style="color: <?php echo $phase_color?>"><?php echo $member->y?></td>
                <td style="color: <?php echo $phase_color?>"><?php echo round($member->intercept, 3)?></td>
                <td style="color: <?php echo $phase_color?>"><?php echo round($member->slope, 3)?></td>
                <td style="color: <?php echo $phase_color?>"><?php echo round($member->steyx, 3)?></td>
                <td style="color: <?php echo $phase_color?>"><?php echo round($member->steyxDiff, 3)?></td>
                <?php
              }
              ?>
              <td style="color: <?php echo $phase_color?>" class="text-center"><input type="checkbox" name="excluded[]" class="point-excluded" value="<?php echo $exclude_ref?>"<?php echo $member->excluded ? ' checked="checked"' : ''?>></td>
            </tr>
            <?php
          }
        }
        ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="6"><small>&sigma; = standard error of the regression</small></td>
          <td class="text-center"><input type="submit" class="btn btn-sm btn-default" name="update" value="Update" /></td>
        </tr>
      </tfoot>
      </table>
      
      <?php
    }

    public function phaseRegressions()
    {
      ?>
      <table class='table'>
      <thead>
        <tr>
          <th colspan="4">Phases</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Phase</td>
          <td>Eq.</td>
          <td>Slope</td>
          <td>Intercept</td>
        </tr>
        <?php
        for($i = 0, $i_count = count($this->phases); $i < $i_count; $i++)
        {
          $phase = (object) $this->phases[$i];
          $calc_phase_intercept = $i < 2;
          $phase_color = $this->getPhaseColor($phase->number);

          echo "<tr>";
          echo "<td style='color:".$phase_color."'>".($phase->number+1)."</td>";
          echo "<td style='color:".$phase_color."'>y = ".round($phase->slope,3)."x + ".round($phase->intercept,3)."</td>";
          echo "<td style='color:".$phase_color."'>".round($phase->slope,3)."</td>";
          echo "<td style='color:".$phase_color."'>".round($phase->intercept,3)."</td>";
          echo "</tr>";
        }
        ?>
      </table>
      <?php
    }
    public function getPhaseColor($number)
    {
      switch((int)$number)
      {
        case 0: return 'red';
        case 1: return 'blue';
        case 2: return 'magenta';
        case 3: return 'orange';
      }
      return '#ccc';
    }
}
