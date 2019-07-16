<?php
/**
 * @author  Semenov Dmitri <Semenov@vdgb-soft.ru>
 * @since 1.0
 * @copyright Copyright (c) 1994-2019 otr@rarus.ru
 * @date 05.07.19 17:56
 */


namespace app\controllers;

use yii\web\Controller;
use yii\data\Pagination;
use app\models\Coupon;
use app\models\Shop;


class CouponController extends Controller
{
	public function actionIndex()
	{
		$query = Coupon::find();

		$pagination = new Pagination(
			[
				'defaultPageSize' => 10,
				'totalCount' => $query->count(),
			]
		);

		$coupons = $query->orderBy('id')
		                 ->offset($pagination->offset)
		                 ->limit($pagination->limit)
		                 ->all();


		return $this->render('index',
			[
				'coupons' => $coupons,
				'pagination' => $pagination,
			]);
	}
}