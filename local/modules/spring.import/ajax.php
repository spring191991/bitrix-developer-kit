<?php
/**
 * Кастомный AJAX-обработчик для асинхронного импорта каталога
 */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Spring\Import\Model\TaskTable;

header('Content-Type: application/json; charset=utf-8');

$request = Application::getInstance()->getContext()->getRequest();
if (!$request->isPost()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Некорректный метод запроса (разрешен только POST).'
    ]);
    die();
}

try {
    if (!Loader::includeModule('spring.import')) {
        throw new \Exception('Системная ошибка: не удалось подключить модуль spring.import.');
    }

    $categoryId = (int)$request->getPost('cat_id');
    if ($categoryId <= 0) {
        throw new \Exception('Не выбрана или указана некорректная категория для импорта товаров.');
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new \Exception('Файл не был загружен на сервер или произошла ошибка при передаче.');
    }

    $uploadedFile = $_FILES['file'];

    $fileExtension = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
    if (strtolower($fileExtension) !== 'xlsx' && strtolower($fileExtension) !== 'xls') {
        throw new \Exception('Недопустимый формат файла. Разрешены только файлы .xlsx или .xls (Excel).');
    }

    $postParams = [
        'proizvoditel' => htmlspecialcharsbx($request->getPost('proizvoditel')),
        'color'        => htmlspecialcharsbx($request->getPost('color')),
        'material'     => htmlspecialcharsbx($request->getPost('material'))
    ];

    $uploadDir = $_SERVER["DOCUMENT_ROOT"] . '/bitrix/tmp/spring.import/';
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $fileName = $_FILES['file']['name'] ?? 'test.xlsx';
    $uploadedFilePath = $uploadDir . htmlspecialcharsbx($fileName);

    move_uploaded_file($uploadedFile['tmp_name'], $uploadedFilePath);

    $result = TaskTable::add([
        'FILE_PATH'   => $uploadedFilePath,
        'STATUS'      => 'NEW',
        'CAT_ID'      => $categoryId,
        'POST_PARAMS' => json_encode($postParams, JSON_UNESCAPED_UNICODE)
    ]);

    if (!$result->isSuccess()) {
        throw new \Exception('Ошибка записи в СУБД: ' . implode(', ', $result->getErrorMessages()));
    }

    echo json_encode([
        'status' => 'success',
        'message' => "Успешно: Файл «{$uploadedFile['name']}» успешно принят сервером. Задача на асинхронный парсинг зарегистрирована в базе под ID: " . $result->getId() . " для категории ID: {$categoryId}."
    ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
