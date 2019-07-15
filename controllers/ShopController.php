<?php
/**
 * @author  Semenov Dmitri <Semenov@vdgb-soft.ru>
 * @since 1.0
 * @copyright Copyright (c) 1994-2019 otr@rarus.ru
 * @date 02.07.19 18:04
 */


namespace app\controllers;

use yii\web\Controller;
use yii\data\Pagination;
use app\models\Shop;


class ShopController extends Controller
{

	public function actionIndex()
	{
		$query = Shop::find();

		$pagination = new Pagination(
			[
				'defaultPageSize' => 20,
				'totalCount' => $query->count(),
			]
		);

		$shops = $query->orderBy('id')
		               ->offset($pagination->offset)
		               ->limit($pagination->limit)
		               ->all();


		return $this->render('index',
			[
				'shops' => $shops,
				'pagination' => $pagination,
			]);
	}
}