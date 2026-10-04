<?php
use \Bitrix\Main\ModuleManager;

class spring_import extends CModule {
    public function __construct(){
        $this->MODULE_ID = 'spring.import';
        $this->MODULE_VERSION = '1.0.0';
        $this->MODULE_VERSION_DATE = '2026-09-27';
        $this->MODULE_NAME = 'Асинхронный импорт товаров';
        $this->MODULE_DESCRIPTION = 'Импортирует товары в каталог сайта';
    }
    public function DoInstall() {
        ModuleManager::registerModule($this->MODULE_ID);
    }

    public function DoUninstall() {
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }
}