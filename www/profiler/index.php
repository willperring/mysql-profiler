<?php

define('PROFILER_APP', true);
include( __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php' );

$files = glob( PROFILE_DIR . DS . '*.dbprofilelog');
$qm    = new QueryManager();

$profiles = array();
foreach( $files as $file ) {
	$profiles[] = new ProfileLog( $file );
}

// Sort the profile files if we're in a detailed view
$qm->paramIsTruthy('detailed') && usort( $profiles, ProfileLog::getComparisonFunction(
	$qm->getParamOrDefault('sort', 'request', true),
	$qm->getParamOrDefault('dir',  'asc',     true)
));

// These must correspond to the relevant column index of the table
$sortFields = array(
	'request' => 'Request Path',
	'save'    => 'Save Time',
	'count'   => 'Query Count',
	'time'    => 'Total Time (ms)',
	'name'    => 'Name',
);

?><!doctype html>
<html>
<head>

	<title>DBProfile Output Viewer</title>

    <style>
        .rename {
            font-size: 0.6em;
            color: #666;
            cursor: pointer;
        }
    </style>

    <script>
        window.selectAll = function( param ) {
        	var fileChecks = document.getElementsByName('files[]');
        	for( var i=0; i<fileChecks.length; i++ ) {
        		fileChecks[i].checked = param;
            }
        };
        window.beginRename = function( file ) {
        	var promptStr = "Enter new new for " + file
				+ "\n(.dbprofilelog will be automatically appended)";

        	var newName = prompt(promptStr);
        	if( !newName )
        		return false;

        	newName = newName + '.dbprofilelog'

        	var confirmStr = "Are you sure you want to rename "
                + file + '\nto\n' + newName + '?';

        	if( confirm( confirmStr ) )
        		window.location = 'rename.php?old=' + file + '&new=' + newName;
        	return false;
        };
    </script>

</head>
<body>

	<p>
	<?php if( $qm->paramIsTruthy('detailed') ): ?>
		<a href="<?=$qm->clone()
			->set('detailed',0)
			->unset(array_keys($sortFields))
			->unset(array('sort', 'dir'))
			->getQueryString();
			?>">Simple View</a>
	<?php else: ?>
		<a href="<?=$qm->clone()->set('detailed',1)->getQueryString();?>">Detailed View</a>
	<?php endif; ?>
	</p>

	<?php if( $qm->paramIsTruthy('detailed') ): ?>
        <form method="get" action="group.php" target="_blank">
            <table>
                <tr>
                    <th><input type="checkbox" onclick="selectAll(this.checked);"/></th>
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
                </tr>
                <?php foreach( $profiles as $profile ): ?>
                <tr>
                    <td><input type="checkbox" name="files[]" value="<?=$profile->getFilename();?>" /></td>
                    <td>
                        <a href="open.php?id=<?=$profile->getFilename();?>" target="_blank">
                            <?=$profile->getRequest();?>
                        </a>
                    </td>
                    <td><?=date('Y-m-d H:i:s', $profile->getSaveTime());?></td>
                    <td><?=$profile->getCount();?></td>
                    <td><?=$profile->getExecutionTime();?></td>
                    <td><?=$profile->getFilename();?> <a class="rename" onclick="beginRename('<?=$profile->getFilename();?>')">Rename?</a></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <button type="submit">Profile Group</button>
            <button type="submit" formaction="delete.php" formtarget="_top"
                    onclick="return confirm('Are you sure you want to proceed?');">Delete Selected</button>
        </form>
	<?php else:
		foreach( $profiles as $profile ):?>
			<a href="open.php?id=<?=$profile->getFilename();?>" target="_blank">
				<?=$profile->getFilename();?></a>
			<br/>
		<?php endforeach;
	endif; ?>

</body>
</html>