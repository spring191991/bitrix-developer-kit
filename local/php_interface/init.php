<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;

Loader::registerAutoLoadClasses(
    null, 
    array(
        "Spring\Main\Logger" => "/local/php_interface/classes/Spring/Main/Logger.php",
        "Spring\Main\ExchangeService" => "/local/php_interface/classes/Spring/Main/ExchangeService.php" 
    )
);

EventManager::getInstance()->addEventHandler(
	"main", 
	"OnMainPageError",
	array(
		"\Spring\Main\Logger", 
		"logError"
	)
);