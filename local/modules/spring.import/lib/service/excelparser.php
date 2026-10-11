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

            if (!\Bitrix\Main\Loader::includeModule('catalog')) {
                throw new \Exception(
                    'Не удалось подключить модуль каталога.'
                );
            }

            $el = new \CIBlockElement;

            $rows = $xlsx->rows();

            if (empty($rows) || count($rows) < 2) {
                throw new \Exception("Файл Excel не содержит строк с данными.");
            }

            $header = array_shift($rows);

            $importedCount = 0; $stringCount = 0;

            $totalRows = count($rows);
            $rows = array_slice($rows, $current_row);

            foreach ($rows as $row) {

                if($stringCount < $chank) {
                    $stringCount++;

                    if (empty($row)) {
                        continue;
                    }

                    $externalId = trim($row[0]);

                    if ($externalId === '') {
                        throw new \Exception(
                            "Не указан EXTERNAL_ID в строке Excel."
                        );
                    }

                    $productName = trim($row[1]);
                    $productArticle = trim($row[2]);
                    $productPrice = (float)$row[3];

                    $fields = [
                        "IBLOCK_ID" => 2, 
                        "IBLOCK_SECTION_ID" => $categoryId, 
                        "NAME" => $productName,
                        "ACTIVE" => "Y", 
                        "CODE" => \CUtil::translit($productName, "ru"), 
                        "PROPERTY_VALUES" => [
                            "EXTERNAL_ID" => $externalId,
                            "ARTICLE" => $productArticle,
                            "PROIZVODITEL" => $postParams['proizvoditel'] ?? '',
                            "COLOR" => $postParams['color'] ?? '',
                            "MATERIAL" => $postParams['material'] ?? '',
                        ]
                    ];

                    // Ищем товар по внешнему ID
                    $existingProduct = \CIBlockElement::GetList(
                        [],
                        [
                            "IBLOCK_ID" => 2,
                            "PROPERTY_EXTERNAL_ID" => $externalId,
                        ],
                        false,
                        ["nTopCount" => 1],
                        ["ID"]
                    )->Fetch();

                    if ($existingProduct) {
                        $productId = (int)$existingProduct["ID"];

                        // Обновляем существующий товар
                        if (!$el->Update($productId, $fields)) {
                            throw new \Exception(
                                "Ошибка обновления товара: " . $el->LAST_ERROR
                            );
                        }
                    } else {
                        $productId = $el->Add($fields);
                    }

                    if ($productId) {
                        if ($productPrice > 0) {
                            
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
