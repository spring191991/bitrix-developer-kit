<?php
namespace Spring\Import\Service;

require_once($_SERVER["DOCUMENT_ROOT"] . "/local/modules/spring.import/vendor/SimpleXLSX.php");

use Shuchkin\SimpleXLSX;

class ExcelParser 
{
    /**
     * Метод для чтения файла Excel
     */
    public function parse(string $filePath, int $categoryId, array $postParams, int $current_row, int $chank = 100): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("Файл не найден по пути: " . $filePath);
        }

        if ($xlsx = SimpleXLSX::parse($filePath)) {
            
            if (!\Bitrix\Main\Loader::includeModule('iblock')) {
                throw new \Exception('Не удалось подключить системный модуль инфоблоков.');
            }

            $el = new \CIBlockElement;

            $rows = $xlsx->rows();

            if (empty($rows)) {
                throw new \Exception("Файл Excel не содержит строк с данными.");
            }

            $header = array_shift($rows);

            $importedCount = 0; $stringCount = 0;

            $totalRows = count($rows);
            $rows = array_slice($rows, $current_row);

            foreach ($rows as $row) {

                if($stringCount < $chank) {
                    $stringCount++;

                    if (empty($row) || empty(trim($row[0]))) {
                        continue;
                    }

                    $productName = trim($row[0]);
                    $productArticle = trim($row[1]);
                    $productPrice = (float)$row[2];

                    $fields = [
                        "IBLOCK_ID" => 2, 
                        "IBLOCK_SECTION_ID" => $categoryId, 
                        "NAME" => $productName,
                        "ACTIVE" => "Y", 
                        "CODE" => \CUtil::translit($productName, "ru"), 
                        "PROPERTY_VALUES" => [
                            "ARTICLE" => $productArticle,
                            "PROIZVODITEL" => $postParams['proizvoditel'] ?? '',
                            "COLOR" => $postParams['color'] ?? '',
                            "MATERIAL" => $postParams['material'] ?? '',
                        ]
                    ];

                    // Создаем товар в БД Битрикса
                    $productId = $el->Add($fields);

                    if ($productId) {
                        if (\Bitrix\Main\Loader::includeModule('catalog') && $productPrice > 0) {
                            
                            \CPrice::SetBasePrice($productId, $productPrice, "RUB");
                        }
                        $importedCount++;
                    } else {
                        throw new \Exception("Ошибка создания товара: " . $el->LAST_ERROR);
                    }
                }
                else {
                    break;
                }
            }
            
            $current_row += $stringCount;
            if ($totalRows <= $current_row) {
                $isComplete = true;
            } else {
                $isComplete = false;
            }

            return ["importedCount" => $importedCount, "nextRow" => $current_row, "isComplete" => $isComplete];

        } else {
            throw new \Exception("Ошибка парсинга Excel: " . SimpleXLSX::parseError());
        }
    }
}
