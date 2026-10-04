<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
?>
<div class="catalog-import">
    <h2 class="catalog-import__title">Импорт товаров</h2>
    
    <form id="spring-import-form" class="catalog-import__form" method="POST" enctype="multipart/form-data">   					
        
        <div class="catalog-import__form-group">
            <label class="catalog-import__label" for="cat_id">Категория для импорта:</label>
            <select class="catalog-import__input catalog-import__input--select" name="cat_id" id="cat_id" required>
                <option value="10">Футболки</option>
                <option value="11">Спортивная Одежда</option>
            </select>
        </div>
        
        <div class="catalog-import__form-group">
            <label class="catalog-import__label" for="proizvoditel">Производитель (Бренд):</label>
            <input class="catalog-import__input" type="text" name="proizvoditel" id="proizvoditel" placeholder="Например: DoorHan или Elyts">
        </div>
        
        <div class="catalog-import__form-group">
            <label class="catalog-import__label" for="color">Цвет номенклатуры:</label>
            <input class="catalog-import__input" type="text" name="color" id="color" placeholder="Например: Антрацит или Черный">
        </div>
        
        <div class="catalog-import__form-group">
            <label class="catalog-import__label" for="material">Материал:</label>
            <input class="catalog-import__input" type="text" name="material" id="material" placeholder="Например: Сталь или Хлопок">
        </div>
            
        <div class="catalog-import__form-group">
            <label class="catalog-import__label" for="file">Файл для импорта:</label>
            <input class="catalog-import__file-input" type="file" name="file" id="file" accept=".xlsx, .xls" required>
            <small class="catalog-import__help-text">Максимальный размер файла — 10 Мб</small>
        </div>

        <div class="catalog-import__form-group">
            <button class="catalog-import__button" type="submit">
                Запустить импорт
            </button>
        </div>
    </form>		

    <!-- Блок для вывода сообщений -->
    <div id="import-message" class="catalog-import__message"></div>
</div>
