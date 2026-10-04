<?php
namespace Spring\Import\Service;
use Spring\Import\Model\TaskTable;

class ImportService{
    public function execute(int $category_id, array $file){
        if($file['error'] !== UPLOAD_ERR_OK) 
            throw new \Exception($file['error']);

        if($file['size'] > (10 * 1024 * 1024)){
            throw new \Exception('Файл слишком большой');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if($extension != "xlsx" && $extension != "xls"){
            throw new \Exception('Не верное расширение файла '.$extension.'. Должно быть xlsx или xls.');
        }

        $dir = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true); 
        }

        $destination = $dir . '' . $file['name'];
        move_uploaded_file($file['tmp_name'], $destination);

        $postData = \Bitrix\Main\Context::getCurrent()->getRequest()->getPostList()->toArray();

        $result = TaskTable::add([
            'FILE_PATH' => $destination,
            'CAT_ID' => $category_id,
            'POST_PARAMS' => json_encode($postData, JSON_UNESCAPED_UNICODE)
        ]);

        if (!$result->isSuccess()) {
            throw new \Exception(implode(', ', $result->getErrorMessages()));
        }
    }
}