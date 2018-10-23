<?php
define('REQUEST_MICROTIME', microtime(true));
define('MODULE_DIR', realpath(__DIR__ . '/../module'));
define('PUBLIC_DIR', realpath(__DIR__));
define('DATA_DIR', realpath(__DIR__ . '/../data'));
define('MAIN_DIR', realpath(__DIR__ . '/..'));
define('MODE', 'public'); //dev | test | public
date_default_timezone_set('Europe/Moscow');

//die('Сайт в разработке');

mb_internal_encoding('UTF-8');

/* Редирект на мобильную версию */
function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

$domain = $_SERVER['HTTP_HOST'];

$domain = $_SERVER['HTTP_HOST'];
if(isMobile() && (empty($_COOKIE['version']) || $_COOKIE['version'] != 'mobile')) {
	if(strpos($domain, 'm.') !== 0) {
		$url = 'https://m.' . $domain . $_SERVER['REQUEST_URI'];
		header('Location: ' . $url, true, 301);
		die();
	}
} else {
	if(strpos($domain, 'm.') === 0) {
		$url = 'https://' . substr($domain, 2) . $_SERVER['REQUEST_URI'];
		header('Location: ' . $url, true, 301);
		die();
	}
}

chdir(dirname(__DIR__));
error_reporting(E_ERROR);

require 'init_autoloader.php';

Zend\Mvc\Application::init(require 'config/application.config.php')->run();