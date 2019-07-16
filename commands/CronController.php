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
		echo "Work!\r\n";

		$this->insertShop();
		echo "Add Shop ... OK\r\n";
		$this->addCoupons();
		echo "Add Coupons ... OK\r\n";
		$this->deleteExpCoupons();
		echo "delete exp Coupons ... OK\r\n";

		return ExitCode::OK;
	}

	public function insertShop()
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
		//return file_get_contents($url);
		return $this->url_get_contents($url);
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

	public function url_get_contents($url, $useragent = 'cURL', $headers = false, $follow_redirects = true, $debug = false)
	{

		// initialise the CURL library
		$ch = curl_init();

		// specify the URL to be retrieved
		curl_setopt($ch, CURLOPT_URL, $url);

		// we want to get the contents of the URL and store it in a variable
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		// specify the useragent: this is a required courtesy to site owners
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);

		// ignore SSL errors
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		// return headers as requested
		if ($headers == true)
		{
			curl_setopt($ch, CURLOPT_HEADER, 1);
		}

		// only return headers
		if ($headers == 'headers only')
		{
			curl_setopt($ch, CURLOPT_NOBODY, 1);
		}

		// follow redirects - note this is disabled by default in most PHP installs from 4.4.4 up
		if ($follow_redirects == true)
		{
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		}

		// if debugging, return an array with CURL debug info and the URL contents
		if ($debug == true)
		{
			$result['contents'] = curl_exec($ch);
			$result['info'] = curl_getinfo($ch);
		}

		// otherwise just return the contents as a variable
		else
		{
			$result = curl_exec($ch);
		}

		// free resources
		curl_close($ch);

		// send back the data
		return $result;
	}
}