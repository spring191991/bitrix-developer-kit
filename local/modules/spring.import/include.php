<?php
use Bitrix\Main\Loader;

// Явно говорим Битриксу, где искать наш класс
Loader::registerAutoLoadClasses(
    'spring.import',
    [
        'Spring\Import\Model\TaskTable' => 'lib/model/task_table.php',
    ]
);
