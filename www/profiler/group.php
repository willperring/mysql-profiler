<?php

define('PROFILER_APP', true);
include( __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php' );

$fileList = @$_GET['files'];

if( !$fileList || !is_array($fileList) || !count($fileList) ) {
	die('No files to profile');
}

$qm = new QueryManager();
$sqlSortParam = $qm->getParamOrDefault('sort', 'time', true);
$sqlSortText  = ( $sqlSortParam == 'time' ) ? 'Sort Alphabetically' : 'Sort by Time' ;
$sqlSortUrl   = $qm->clone()->toggle('sort', array('time','sql'))->getQueryString();
$sqlSortLink  = "<a href=\"{$sqlSortUrl}\">{$sqlSortText}</a>";

$profileGroup  = new ProfileGroup();
$filesNotFound = array();
foreach( $fileList as $file ) {
	$filePath = PROFILE_DIR . DS . $file;

	if( !file_exists($filePath) ) {
		$filesNotFound[] = $file;
		continue;
	}

	$profileLog = new ProfileLog( $filePath );
	$profileGroup->addProfileLog( $profileLog );
}

$profiles   = $profileGroup->getProfiles();
if( !count($profiles) )
    die('No files found to load');

$queries    = $profileGroup->getGroupedQueriesList( $sqlSortParam );
$totalTime  = $queries->getTotalChildValues();
$totalCount = $queries->getDescendantCount();

//$clr = Interpolator::factory('gradient')
$warningGradient = new Gradient();
$warningGradient->addStop( new HexColor(0, 255, 0) )
	->addStop( new HexColor(255,255,0) )
	->addStop( new HexColor( 255, 0, 0) );

$timeMetrics = $profileGroup->getTimeMetrics();
$tMetricStr  = new ObjectDecorator($timeMetrics);
$tMetricStr  = new MathDecorator($tMetricStr, 1000, MathDecorator::OPERATOR_MULTIPLY );
$tMetricStr  = new DecimalDecorator($tMetricStr);
$tMetricStr  = new StringDecorator($tMetricStr, "{{value}}ms");

$gd = Interpolator::factory('percentage', $timeMetrics->quartile25(), $timeMetrics->quartile75() );
$gd = new LimitDecorator($gd, 0, 100);
$gd = new GradientDecorator($gd, $warningGradient);
$gd = new StringDecorator($gd, 'style="background-color:{{value}};"');

$pc = Interpolator::factory('percentage', 0, $totalTime);
$pc = new DecimalDecorator($pc);
$pc = new StringDecorator($pc, '<span class="percent">({{value}}%)</span>');

?><!doctype html>
<html>
<head>
	<style>
		table {
			font-family: sans-serif;
			line-height: 1.5em;
		}
		table#groupDetails {
			font-size: 0.8em;
		}
		table#groupDetails th,
		table#groupDetails td {
			vertical-align: top;
			text-align: left;
		}
        table#groupDetails td {
            padding-left:  0.5em;
            padding-right: 0.5em;
        }
		table#groupDetails ul {
			margin: 0;
			padding: 0;
			list-style: none inside none;
		}
		table#groupProfile {
			font-size: 0.65em;
			border-collapse: collapse;
		}
		table#groupProfile td {
			vertical-align: top;
			padding-right: 0.5em;
			padding-left:  0.5em;
			white-space: pre;
			border-left: 1px solid #ccc;
		}
        table#groupProfile td.noborder {
            border-left: none;
        }
		td.odd {
			background: #eee;
		}
		td:first-child, th:first-child {
			border-left: none;
		}
		span.percent {
			font-size: 80%;
			color: #666;
            display: block;
		}
        td.highlight {
            background-color: yellow !important;
            /*font-weight: bold !important;*/
        }
        .whiteText {
            font-weight: bold;
            color: white;
            text-decoration: none !important;
        }
        a.noLine {
            text-decoration: none !important;
        }
        .ra {
            text-align: right !important;
        }
	</style>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
        window.toggleHighlight = function( el ) {
        	el.classList.toggle('highlight');
        }
    </script>

</head>
<body>

	<table id="groupDetails">
		<?php $th = false; foreach( $profiles as $profile ): ?>
        <tr>
			<?php if( !$th ): $th = true; ?>
            <th rowspan="<?=count($profiles);?>">Files:</th>
            <?php endif; ?>
            <td class="ra" style="background-color: <?=$profile->getMeta('color')->asHex();?>;">
                <span class="whiteText"><?=$profile->getMeta('request');?></span>
            </td>
			<td>
                <a href="open.php?id=<?=$profile->getFilename();?>" target="_blank"><?=$profile->getRequest();?></a>
            </td>
            <td class="ra"><?=number_format($profile->getExecutionTime(), 2);?>ms</td>
            <td class="ra"><?=$profile->getCount();?> queries</td>
            <td><?=date('Y-m-d H:i:s', $profile->getSaveTime());?></td>
		</tr>
        <?php endforeach; ?>
		<tr>
			<th>Query Count</th>
			<td colspan="4"><?=$totalCount;?></td>
		</tr>
		<tr>
			<th>Query Time (ms)</th>
			<td colspan="4"><?=$tMetricStr->getValue('sum');?></td>
		</tr>
		<tr>
			<th>Longest Query</th>
			<td colspan="4"><?=$tMetricStr->getValue('max');?></td>
		</tr>
        <tr>
            <th>IQR</th>
            <td colspan="4">
                <?=$tMetricStr->getValue('quartile25').' - '.$tMetricStr->getValue('quartile75');?></td>
            </td>
        </tr>
	</table>

    <div id="chart"></div>

	<table id="groupProfile">
		<tr>
			<th>Table</th>
			<th>Time (ms)</th>
			<th>Count</th>
			<th>Type</th>
			<th>Time (ms)</th>
			<th>Count</th>
			<th>Time (ms)</th>
            <th></th>
			<th style="text-align: left;">SQL <?=$sqlSortLink;?></th>
		</tr>
		<?php $tableCount = 0; $typeCount = 0; $queryCount = 0;
		foreach( $queries as $tableMetric ):
			$tableAnnounced = false;
			foreach( $tableMetric->getChildList() as $typeMetric ):
				$typeAnnounced = false;
				foreach( $typeMetric->getChildList() as $queryMetric ):
					echo "<tr>";
						if( ! $tableAnnounced ) {
							$rows   = $tableMetric->getDescendantCount();
							$odd    = ( ++$tableCount % 2 == 0 ) ? "even" : "odd";
							$time   = number_format($tableMetric->value * 1000,2);
							$timePc = $pc->getValue( $tableMetric->value );
							echo "<td class=\"{$odd}\" rowspan=\"{$rows}\">{$tableMetric->getMeta('displayTable')}</td>";
							echo "<td class=\"{$odd}\" rowspan=\"{$rows}\">{$time}  {$timePc}</td>";
							echo "<td class=\"{$odd}\" rowspan=\"{$rows}\">{$rows}</td>";
							$tableAnnounced = true;
						}
						if( ! $typeAnnounced ) {
							$rows = $typeMetric->getDescendantCount();
							$odd  = ( ++$typeCount % 2 == 0 ) ? "even" : "odd";
							echo "<td class=\"{$odd}\" rowspan=\"{$rows}\">{$typeMetric->name}</td>";
							echo "<td class=\"{$odd}\" rowspan=\"{$rows}\">".number_format($typeMetric->value * 1000,2)."</td>";
							echo "<td class=\"{$odd}\" rowspan=\"{$rows}\">{$rows}</td>";
							$typeAnnounced = true;
						}
						$odd          = ( ++$queryCount % 2 == 0 ) ? "even" : "odd";
						$requestColor = "background-color: {$queryMetric->getMeta('color')->asHex()};";
						echo "<td class=\"{$odd} ra\" {$gd->getValue($queryMetric->value)}>".number_format($queryMetric->value * 1000, 2)."</td>";
						echo "<td class=\"{$odd} ra noborder\" style=\"{$requestColor}\">"
                            . "<a class=\"noLine\" href=\"#groupDetails\">"
                            . "<span class=\"whiteText\">{$queryMetric->getMeta('request')}</span>"
                            . "</a></td>";
						echo "<td class=\"{$odd} noborder sql\" onclick=\"toggleHighlight(this)\">{$queryMetric->name}</td>";
					echo "</tr>";
				endforeach;
			endforeach;
		endforeach; ?>
	</table>

    <script type="text/javascript">
		const sqlTDs = document.querySelectorAll('td.sql');
		for( var i=0; i<sqlTDs.length; i++ ) {

			var sqlTD = sqlTDs[i];

			sqlTD.ondblclick = function() {
				document.execCommand('copy');
				return false;
			};

			sqlTD.addEventListener( 'copy', function () {
				event.preventDefault();
				if ( event.clipboardData ) {
					console.log( this.innerText );
					event.clipboardData.setData( "text/plain", this.innerText );
					alert('Copied!');
				}
			} );
		}
    </script>

    <script type="text/javascript">
        var chartData = <?=json_encode( $profileGroup->getChartData() );?>;

        google.charts.load("current", {packages:["corechart"]});
		google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
			var data = google.visualization.arrayToDataTable( chartData );
			var options = {
				title: "Queries by Execution Time(ms)",
                bar: {
					gap: 0
                },
                animation: {
					startup: true
                },
                chartArea: {
					height: 150,
                    width: 600
				},
                histogram: {
					minValue: 0,
					bucketSize: 0.1,
                    maxNumBuckets:300
                },
                hAxis: {
					format: 'short',
                    //showTextEvery: 10,
                    ticks: [0, 5, 10, 10, 20],
                    gridlines: {
						count: 2
                    }
                }
			};

			var chart = new google.visualization.Histogram(
				document.getElementById( 'chart' )
			);

			chart.draw( data, options );
		};
    </script>

</body>
</html>