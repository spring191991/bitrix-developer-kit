<?php
$_SERVER["DOCUMENT_ROOT"] = realpath(__DIR__ . "/../../../..");

// Отключаем сбор статистики Битрикса, чтобы консольный скрипт не тратил лишнюю память
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Spring\Import\Model\TaskTable;
use Spring\Import\Service\ExcelParser;

try {
    if (!Loader::includeModule('spring.import')) {
        die("Системная ошибка: модуль spring.import не найден.\n");
    }

    $task = TaskTable::getList([
        'filter' => [
            '@STATUS' => ['NEW', 'PROCESSING']
        ],
        'order'  => ['ID' => 'ASC'], // Сортируем от старых к новым
        'limit'  => 1
    ])->fetch();

    if (!$task) {
        die("Очередь пуста. Новых задач для импорта нет.\n");
    }

    TaskTable::update($task['ID'], ['STATUS' => 'PROCESSING']);

    $postParams = json_decode($task['POST_PARAMS'], true) ?: [];

    $parser = new ExcelParser();
    
    $result = $parser->parse($task['FILE_PATH'], (int)$task['CAT_ID'], $postParams, $task['CURRENT_ROW']);
    $processedCount = $result['importedCount'];
    $current_row = $result['nextRow'];
   
    if($result['isComplete'] == true){
        $updateResult = TaskTable::update($task['ID'], [
            'STATUS' => 'SUCCESS',
            'CURRENT_ROW' => $current_row
        ]);

        if (!$updateResult->isSuccess()) {
            throw new \Exception(
                'Не удалось обновить статус задачи: ' .
                implode(', ', $updateResult->getErrorMessages())
            );
        }

        if (is_file($task['FILE_PATH'])) {
            unlink($task['FILE_PATH']);
        }

        echo "Успех! Задача ID {$task['ID']} выполнена.\n";
    }
    else{
        $updateResult = TaskTable::update($task['ID'], [
            'CURRENT_ROW' => $current_row
        ]);

        if (!$updateResult->isSuccess()) {
            throw new \Exception(
                'Не удалось обновить статус задачи: ' .
                implode(', ', $updateResult->getErrorMessages())
            );
        }
        echo "Задача ID {$task['ID']} выполняется.\n";
    }

} catch (\Throwable $e) {
    if (isset($task['ID'])) {
        TaskTable::update($task['ID'], ['STATUS' => 'ERROR']);
    }
    die("Ошибка при выполнении фоновой задачи: " . $e->getMessage() . "\n");
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
