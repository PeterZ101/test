<?php

use yii\helpers\Html;
use yii\widgets\LinkPager;

?>

	<table>
		<thead>
		<tr>
			<td colspan="2">
				Coupon
			</td>
		</tr>
		<tbody>
		<? foreach ($coupons as $coupon): ?>
			<tr>
				<td>
					<img src="<?=$coupon->img?>"/>
				</td>
				<td>
					<i><?=$coupon->title?></i><br/>
					<?=$coupon->text?><br/>
					<b><?=$coupon->date_exp?></b>
				</td>
			</tr>
		<? endforeach; ?>
		</tbody>
	</table>


<?=LinkPager::widget(['pagination' => $pagination])?>