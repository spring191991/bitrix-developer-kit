<?php
use \Bitrix\Main\ModuleManager;
use Bitrix\Main\Application;
use Spring\Import\Model\TaskTable;

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
    public function InstallDB() {
        // Подключаем наш модуль, чтобы Битрикс увидел класс TaskTable
        if (\Bitrix\Main\Loader::includeModule($this->MODULE_ID)) {
            $connection = Application::getConnection();
            if (!$connection->isTableExists(TaskTable::getTableName())) {
                TaskTable::getEntity()->createDbTable();
            }
        }
    }

    public function UnInstallDB() {
        if (\Bitrix\Main\Loader::includeModule($this->MODULE_ID)) {
            $connection = Application::getConnection();
            if ($connection->isTableExists(TaskTable::getTableName())) {
                $connection->dropTable(TaskTable::getTableName());
            }
        }
    }
}
