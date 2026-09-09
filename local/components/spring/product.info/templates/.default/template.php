<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<div class="product-card">
	<h1 class="product-card__title">
		<?= $arResult['PRODUCT']['NAME']; ?>
	</h1>
	<div class="product-card__price">
		Цена: <?= $arResult['PRODUCT']['PRODUCT_PRICE']; ?>
	</div>
</div>
