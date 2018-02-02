<?php

define('PROFILER_APP', true);
include( __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php' );

$fileId   = @$_GET['id'];
$fileName = PROFILE_DIR . DS . $fileId;

if( empty($fileId) )
	die('No id supplied');
if( !file_exists($fileName) )
	die('File doesn\'t exist');

$profile = new ProfileLog( $fileName );
$queries = $profile->getQueries();
$qm      = new QueryManager();
$idSeq   = new IdSequencer();

$sortFields = array(
	'start' => 'Start',
	'table' => 'Table',
	'type'  => 'Type',
	'time'  => 'Time',
	'sql'   => 'SQL',
);

usort( $queries, QueryLog::getComparisonFunction(
	$qm->getParamOrDefault('sort', 'start', true),
	$qm->getParamOrDefault('dir',  'asc',   true)
));


?><!doctype html>
<html>
<head>
	<title>DBProfile Viewer</title>

	<style>
		html, body {
			width: 100%;
			max-width: 100%;
		}
        table {
            border-collapse: collapse;
        }
        td {
            padding-top:   0.4em;
            padding-left:  0.8em;
            padding-right: 0.8em;
        }
        tr.odd, tr.odd td {
            background: #eee;
        }
		#fileDetails th {
			text-align: right;
			padding-right: 1em;
		}
		#queryDetails {
			width: 90%;
		}
        #queryDetails tr.trace {
            display: none;
        }
        #queryDetails tr.visible {
            display: table-row;
        }
        #queryDetails td {
			vertical-align: top;
			font-size: 0.8em;
			font-family: sans-serif;
			white-space: normal;
			word-wrap: break-word;
		}
        #queryDetails td.sql,
        #queryDetails tr.trace > td {
            padding-bottom: 1em;
        }
		#queryDetails td.sql {
			width: 60%;
		}
        #chart {
            margin-top: 1em;
        }
        div.tooltip {
            color: purple;
        }
        div.tooltip span {
            display: block;
        }
        .toggleTrace, .toggleArgs {
            cursor: pointer;
            text-decoration: underline;
        }
        .argumentList {
            display: none;
            white-space: pre-wrap;
        }
        .argumentList.visible {
            display: block;
        }

	</style>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
        window.toggleTrace = function( id ) {
        	var rowId = 'trace-' + id,
                rowEl = document.getElementById( rowId );
        	rowEl.classList.toggle('visible');
        };
        window.toggleArgs = function( id ) {
        	var argId = "args-" + id,
                argEl = document.getElementById( argId );
        	argEl.classList.toggle('visible');
        };

    </script>

</head>
<body>

    <table id="fileDetails">
        <tr>
            <th>Filename</th>
            <td><?=$profile->getFilename();?></td>
        </tr>
        <tr>
            <th>Request</th>
            <td><?=$profile->getRequest();?></td>
        </tr>
        <tr>
            <th>Script</th>
            <td><?=$profile->getScript();?></td>
        </tr>
        <tr>
            <th>Total Execution Time (ms)</th>
            <td><?=$profile->getExecutionTime();?></td>
        </tr>
        <tr>
            <th>Query Count</th>
            <td><?=$profile->getCount();?></td>
        </tr>
    </table>

    <div id="chart"></div>

    <table id="queryDetails">
        <tr>
            <?php foreach( $sortFields as $id => $display ):
                $path = ( $qm->getParam('sort') == $id )
                    ? $qm->clone()
                        ->toggle('dir', array('asc','desc'))
                    : $qm->clone()
                        ->set('sort', $id)
                        ->unset('dir');
                ?>
                <th>
                    <a href="<?=$path->getQueryString();?>"><?=$display;?></a>
                </th>
            <?php endforeach; ?>
            <th>Result</th>
        </tr>
        <?php $i = -1; foreach( $queries as $query ): $i++; ?>
            <tr class="<?=($i%2==0?"even":"odd")?>">
                <td><?=$query->getStartTimeString();?></td>
                <td><?=$query->getFullTable();?></td>
                <td>
                    <a class="toggleTrace" onclick="toggleTrace('<?=$idSeq->get();?>')">
                        <?=$query->getType();?>
                    </a>
                </td>
                <td><?=$query->getExecutionTimeMs();?></td>
                <td class="sql"><?=$query->getSql(30);?></td>
                <td><?=$query->getResult(30);?></td>
            </tr>
            <tr class="trace <?=($i%2==0?"even":"odd")?>" id="trace-<?=$idSeq->repeat();?>">
                <td>TRACE:</td>
                <td colspan="4">
                    <table>
                        <tr>
                            <th>File</th>
                            <th>Line</th>
                            <th>Function</th>
                        </tr>
                        <?php foreach( $query->getTrace() as $traceRow ): ?>
                            <tr>
                                <td><?=$traceRow['file'];?></td>
                                <td><?=$traceRow['line'];?></td>
                                <td><?php
                                    $hasArguments = count($traceRow['args']) > 0;
                                    $functionName = ($traceRow['class']
                                        ? "{$traceRow['class']}{$traceRow['type']}" : '')
                                        . $traceRow['function'];
                                    if( $hasArguments ) {
                                        echo "<a class=\"toggleArgs\" onclick=\"toggleArgs('".$idSeq->get()."')\">";
                                        echo $functionName;
                                        echo "</a> (";
                                        echo count($traceRow['args']);
                                        echo " Arguments)";
                                        echo "<div class=\"argumentList\" id=\"args-".$idSeq->repeat()."\">";
                                        echo print_r($traceRow['args'], true);
                                        echo "</div>";
                                    } else {
                                        echo $functionName;
                                    }
                                ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </td>
            </tr>
        <?php endforeach; ?>
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
        var chartData = <?=json_encode( $profile->getChartData() );?>;

        google.charts.load("current", {packages:["timeline"]});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {

            var dataTable = new google.visualization.DataTable();
            dataTable.addColumn({ type: 'string', id:'RowLabel' });
            dataTable.addColumn({ type: 'string', id:'BarLabel' });
			dataTable.addColumn({ type: 'string', role:'tooltip', p:{html:true} });
			dataTable.addColumn({ type: 'number', id:'Start'    });
			dataTable.addColumn({ type: 'number', id:'End'      });
			dataTable.addRows( chartData.data );

            var options = {
                title: 'Query Activity',
				height: (( chartData.tables + 1.5  ) * 40 ) + 10,
                colorByRowTitle: true,
                animation: {
                	startup: true
                },
                tooltip: {
                	isHtml: true
                },
                hAxis: {
                	textPosition: 'none'
                },
                width: document.getElementById('queryDetails').offsetWidth
            };

            var chart = new google.visualization.Timeline(
            	document.getElementById('chart')
            );

            chart.draw( dataTable, options );
        }
    </script>

</body>
</html>

