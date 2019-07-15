<?php

use yii\helpers\Html;
use yii\widgets\LinkPager;

?>

	<h1>Shops</h1>
	<ul>
		<? foreach ($shops as $shop): ?>
			<li>
				<?=Html::encode("{$shop->name} ({$shop->url})")?>
				<?//var_dump($shop->coupons);?>
			</li>
		<? endforeach; ?>
	</ul>


<?=LinkPager::widget(['pagination' => $pagination])?>