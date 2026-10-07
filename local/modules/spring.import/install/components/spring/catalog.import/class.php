<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Spring\Import\Model\TaskTable;

class CatalogImportComponent extends CBitrixComponent
{
    /**
     * Точка входа в компонент, запускается автоматически при IncludeComponent
     */
    public function executeComponent()
    {
        if (!Loader::includeModule('spring.import')) {
            ShowError('Модуль spring.import не установлен!');
            return;
        }

        $this->handlePostRequest();
        $this->includeComponentTemplate();
    }

    /**
     * Логика обработки загрузки файла и полей формы
     */
    protected function handlePostRequest()
    {
        $request = Application::getInstance()->getContext()->getRequest();

        if ($request->isPost() && !empty($_FILES['file'])) {
            
            $catId = (int)$request->getPost('cat_id');
            $proizvoditel = htmlspecialcharsbx($request->getPost('proizvoditel'));
            $color = htmlspecialcharsbx($request->getPost('color'));
            $material = htmlspecialcharsbx($request->getPost('material'));

            $uploadedFilePath = '/var/www/html/' . htmlspecialcharsbx($_FILES['file']['name']);

            $postParams = [
                'proizvoditel' => $proizvoditel,
                'color' => $color,
                'material' => $material
            ];

            $result = TaskTable::add([
                'FILE_PATH' => $uploadedFilePath,
                'STATUS' => 'NEW',
                'CAT_ID' => $catId,
                'POST_PARAMS' => json_encode($postParams, JSON_UNESCAPED_UNICODE)
            ]);

            if ($result->isSuccess()) {
                $this->arResult['SUCCESS_MESSAGE'] = 'Файл добавлен в очередь на асинхронный импорт. ID задачи: ' . $result->getId();
            } else {
                $this->arResult['ERROR_MESSAGE'] = 'Ошибка сохранения в базу данных: ' . implode(', ', $result->getErrorMessages());
            }
        }
    }
}
