<?php
namespace Spring\Main; 
use \Bitrix\Main\Application;

class Logger 
{
    public function logError($errorMessage) 
    {
        // Здесь будет логика записи в файл
		if(is_object($errorMessage)) $errorMessage = $errorMessage->getMessage(); 
		$data = "[".date('d.m.Y H:i:s')."] ".$errorMessage."\n";
		$bytesWritten = file_put_contents(Application::getDocumentRoot()."/local/logs/app_errors.log", $data, FILE_APPEND);

		if ($bytesWritten === false) {
			return "Failed to write to file.";
		}
    }
}