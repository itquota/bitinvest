<?php

header("access-control-allow-origin: *");

// Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', dirname(__FILE__) . '/application');

// Define base path for js/css and image files
defined('BASEPATH') || define('BASEPATH','/gbc_ui31');

//defined('BASE') || define('BASE',$_SERVER['HTTP_HOST']."/gbc");

date_default_timezone_set('Asia/Kolkata');

if (!empty($_SERVER['HTTPS']) || !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    defined('BASE') || define('BASE',"https://".$_SERVER['HTTP_HOST']."/gbc_ui31");
}else{
    defined('BASE') || define('BASE',"http://".$_SERVER['HTTP_HOST']."/gbc_ui31");
}
header("X-Frame-Options: SAMEORIGIN");

// Define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
;

defined('REFUND_DATE') || define('REFUND_DATE','2016-10-01 00:00:00');
defined('TOKENMSG') || define('TOKENMSG','Invalid Token');
defined('MSGusername') || define('MSGusername','7827572892');
defined('MSGhash') || define('MSGhash','amit123456');
defined('MSGsender') || define('MSGsender',urlencode('GBCOIN'));
defined('MaxPayoutLimit') || define('MaxPayoutLimit','100');


defined('FILE_UPLOAD_PATH') || define('FILE_UPLOAD_PATH',dirname(__FILE__)."");
// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library') . PATH_SEPARATOR . realpath(APPLICATION_PATH . '/models'),
    get_include_path(),
)));

ini_set("memory_limit", "1024M");
set_time_limit(0);

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

// routes for this application

$ctrl  = Zend_Controller_Front::getInstance();
$router = $ctrl->getRouter();

// routes for index controller
//$router->addRoute('jpmiles', new Zend_Controller_Router_Route('/jpmiles',array('controller'=>'index','action'=>'cc')));
// routes for admin controller
$router->addRoute('login', new Zend_Controller_Router_Route('/',array('controller'=>'Login','action'=>'index')));
//$router->addRoute('changepassword', new Zend_Controller_Router_Route('/changepassword',array('controller'=>'admin','action'=>'changepassword')));
//$router->addRoute('logout', new Zend_Controller_Router_Route('/logout',array('controller'=>'admin','action'=>'logout')));

$application->bootstrap()
    ->run();
