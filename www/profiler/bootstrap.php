<?php

if( !defined('PROFILER_APP') )
	die('No direct access');

define('DS', DIRECTORY_SEPARATOR);

define('CLASS_DIR', __DIR__ . DS . 'classes' . DS );
// Interfaces first...
require_once( CLASS_DIR . 'ValueDecoratable.php'  );
// ...then traits...
require_once( CLASS_DIR . 'Metadata.php'          );
// ...then abstracts...
require_once( CLASS_DIR . 'ValueDecorator.php'        );
require_once( CLASS_DIR . 'CacheableResultObject.php' );
// ...then classes:
require_once( CLASS_DIR . 'DecimalDecorator.php'  );
require_once( CLASS_DIR . 'Gradient.php'          );
require_once( CLASS_DIR . 'GradientDecorator.php' );
require_once( CLASS_DIR . 'HexColor.php'          );
require_once( CLASS_DIR . 'IdSequencer.php'       );
require_once( CLASS_DIR . 'Interpolator.php'      );
require_once( CLASS_DIR . 'LimitDecorator.php'    );
require_once( CLASS_DIR . 'MathDecorator.php'     );
require_once( CLASS_DIR . 'MetricList.php'        );
require_once( CLASS_DIR . 'NumberList.php'        );
require_once( CLASS_DIR . 'ObjectDecorator.php'   );
require_once( CLASS_DIR . 'ProfileGroup.php'      );
require_once( CLASS_DIR . 'ProfileLog.php'        );
require_once( CLASS_DIR . 'QueryLog.php'          );
require_once( CLASS_DIR . 'QueryManager.php'      );
require_once( CLASS_DIR . 'SortableMetric.php'    );
require_once( CLASS_DIR . 'StringDecorator.php'   );
require_once( CLASS_DIR . 'ValueEchoChamber.php'  );

define('PROFILE_DIR', dirname(dirname(__DIR__)) . DS . 'dblogs');

date_default_timezone_set('Europe/Rome');
