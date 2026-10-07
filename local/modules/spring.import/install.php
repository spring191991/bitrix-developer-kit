<?php
use Spring\Import\Model\TaskTable;

$connection = \Bitrix\Main\Application::getConnection();
if (!$connection->isTableExists(TaskTable::getTableName())) {
    TaskTable::getEntity()->createDbTable();
}
