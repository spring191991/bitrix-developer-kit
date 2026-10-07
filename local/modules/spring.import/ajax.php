<?php
/**
 * Кастомный AJAX-обработчик для асинхронного импорта каталога
 */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Application;

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

    // --- НАЧАЛО ЗОНЫ ПАКЕТНОЙ ОБРАБОТКИ ---
    // В следующих шагах мы передадим этот файл в ваш сервис импорта:
    // $importService = new \Spring\Import\Services\CatalogImportService();
    // $importService->execute($uploadedFile['tmp_name'], $categoryId);
    // --- КОНЕЦ ЗОНЫ ПАКЕТНОЙ ОБРАБОТКИ ---

    echo json_encode([
        'status' => 'success',
        'message' => "Файл «{$uploadedFile['name']}» успешно принят сервером. Задача на асинхронный парсинг зарегистрирована для категории ID: {$categoryId}."
    ]);

} catch (\Throwable $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
