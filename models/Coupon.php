<?php

namespace app\models;

use yii\db\ActiveRecord;

/*
 *
	DROP TABLE IF EXISTS `coupon`;
	CREATE TABLE `coupon` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `shop_id` int(11) NOT NULL,
	  `title` varchar(500) NOT NULL,
	  `text` varchar(5000) NOT NULL,
	  `date_exp` date NOT NULL,
	  `img` varchar(1000) NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 *
 */


class Coupon extends ActiveRecord
{
	public function getShop()
	{
		return $this->hasOne(Shop::className(),['id' => 'shop_id']);
	}
}
