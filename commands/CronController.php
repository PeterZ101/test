<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;

use app\models\Coupon;
use app\models\Shop;

class CronController extends Controller
{
	public $siteUrl = 'https://www.coupons.com';
	public $shopUrl = "/store-loyalty-card-coupons/";

	public function actionIndex()
	{
		echo "Work\r\n";

		$this->addShop();
		echo "Add Shop\r\n";
		$this->addCoupons();
		echo "Add Coupons\r\n";
		$this->deleteExpCoupons();
		echo "delete exp Coupons\r\n";

		return ExitCode::OK;
	}

	public function addShop()
	{
		$dbShop = Shop::find()->orderBy('id desc')->limit(1)->one();
		$name = '';
		if (!is_null($dbShop))
		{
			$name = $dbShop->name;
		}

		$this->addNextShop($name);
	}

	public function addNextShop($name = '')
	{
		$shop = $this->getNextShop($name);
		if (count($shop) == 3)
		{
			$dbShop = Shop::find()
			             ->where(['name' => $shop[2]])
			             ->limit(1)->one();
			if (is_null($dbShop))
			{
				$dbShop = new Shop();
			}
			$dbShop->name = $shop[2];
			$dbShop->url = $shop[1];
			$dbShop->up_date = date('Y-m-d H:i:s', strtotime('now'));
			$dbShop->save();
		}
	}

	public function getNextShop($name = '.*?')
	{
		$regexp[0] = '/<a href=[\'"](\S*?)[\'"] ';
		$regexp[1] = 'class=[\'"]store-pod[\'"]\s*\S*\s*';
		$regexp[2] = 'title=[\'"](' . $name . ')[\'"] data-storeid=/is';

		$regExpShop = implode($regexp);

		$html = $this->getHtml($this->siteUrl . $this->shopUrl);
		$offset = 0;
		preg_match(
			$regExpShop,
			$html,
			$result,
			PREG_OFFSET_CAPTURE,
			$offset
		);

		if ($name != '.*?')
		{
			foreach ($result as $res)
			{
				$offset = $res[1];
			}

			$regexp[2] = 'title=[\'"](.*?)[\'"] data-storeid=/is';
			$regExpShop = implode($regexp);

			preg_match(
				$regExpShop,
				$html,
				$result,
				PREG_OFFSET_CAPTURE,
				$offset
			);

		}

		$return = [];

		foreach ($result as $res)
		{
			$return[] = $res[0];
		}

		return $return;
	}

	public function getHtml($url = '')
	{
		return file_get_contents($url);
	}

	public function addCoupons()
	{
		$dbShop = Shop::find()->orderBy(['up_date' => 'asc'])->limit(1)->one();

		if (!is_null($dbShop))
		{
			$this->getCoupons($dbShop->id);
			$dbShop->up_date = date('Y-m-d H:i:s', strtotime('now'));

			return $dbShop->save();
		}

		return false;
	}

	public function getCoupons($shopId = 0)
	{
		$return = 0;

		$dbShop = Shop::find()
		            ->where(['id' => $shopId])
		            ->limit(1)->one();

		if (!is_null($dbShop))
		{
			$html = $this->getHtml($this->siteUrl . $dbShop->url);

			$regexp = '/';
			$regexp .= '<div class=["\']pod\s*ci-grid\s*recommended\s*desktop\s*["\'].*?>';
			$regexp .= '.*?<img.*?src=["\'](.*?)["\']\s*\/>';
			$regexp .= '.*?<p class=[\'"]pod_brand[\'"].*?>(.*?)<\/p>';
			$regexp .= '.*?<p class=[\'"]pod_description[\'"].*?>(.*?)<\/p>';
			$regexp .= '.*?Exp: ([0-9\/]*)<\/p>.*?Details:<\/strong>(.*?)<\/div>.*?';
			$regexp .= '<\/div><\/div><\/div>';
			$regexp .= '/is';


			preg_match_all($regexp, $html, $matchesCoupons, PREG_SET_ORDER);


			foreach ($matchesCoupons as $coupon)
			{

				$dbCoupon = Coupon::find()
				                  ->where(['title' => $coupon[3]])
				                  ->limit(1)->one();
				if (is_null($dbCoupon))
				{
					$dbCoupon = new Coupon();
				}
				$dbCoupon->shop_id = $dbShop->id;
				$dbCoupon->img = $coupon[1];
				$dbCoupon->title = $coupon[3];
				$dbCoupon->date_exp = date('Y-m-d', strtotime($coupon[4]));
				$dbCoupon->text = $coupon[5];
				if ($dbCoupon->save())
				{
					++$return;
				}
			}

		}

		return $return;
	}

	public function deleteExpCoupons()
	{
		return Coupon::deleteAll(['<', 'date_exp', date('Y-m-d')]);
	}
}