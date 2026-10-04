<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Engine\CurrentUser;
use \Bitrix\Iblock\ElementTable;
use \Bitrix\Catalog\PriceTable;

class ProductInfoComponent extends \CBitrixComponent 
{
    public function executeComponent()
    {
        $user = CurrentUser::get()->getId();
        
        if ($user > 0) {
            
            if (!isset($this->arParams["CACHE_TIME"])) {
                $this->arParams["CACHE_TIME"] = 3600;
            }

            if ($this->StartResultCache()) {
                
                // Подключаем модули инфоблоков и каталога
                \Bitrix\Main\Loader::includeModule('iblock');
                \Bitrix\Main\Loader::includeModule('catalog');

                $productId = 4; // Наш проверенный ID товара

                // 1. Запрос к таблице элементов (получаем NAME)
                $resElement = ElementTable::getList(array(
                    'select' => array('ID', 'NAME'), 
                    'filter' => array('=IBLOCK_ID' => 2, '=ID' => $productId)
                ));
                $productData = $resElement->fetch();
                
                if ($productData) {
                    $this->arResult['PRODUCT'] = $productData;

                    // 2. Запрос к таблице цен торгового каталога (получаем PRICE)
                    $resPrice = PriceTable::getList(array(
                        'select' => array('PRICE', 'CURRENCY'),
                        'filter' => array('=PRODUCT_ID' => $productId)
                    ));
                    
                    if ($priceData = $resPrice->fetch()) {
                        $this->arResult['PRODUCT']['PRODUCT_PRICE'] = number_format($priceData['PRICE'], 2, '.', ' ') . ' ' . $priceData['CURRENCY'];
                    } else {
                        $this->arResult['PRODUCT']['PRODUCT_PRICE'] = 'Цена не установлена';
                    }
                } else {
                    $this->arResult['PRODUCT'] = array(
                        'NAME' => 'Товар не найден',
                        'PRODUCT_PRICE' => '—'
                    );
                }

                $this->includeComponentTemplate();
            }
        } else {
            echo "Для просмотра информации о товаре необходимо авторизоваться";
            return null;
        }  
    }
}
