<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 *
	DROP TABLE IF EXISTS `shop`;
	CREATE TABLE `shop` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(500) NOT NULL,
	`url` varchar(500) NOT NULL,
	`up_date` datetime DEFAULT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 *
 */

class Shop extends ActiveRecord
{
	public function getCoupons()
	{
		return $this->hasMany(Coupon::className(), ['shop_id' => 'id']);

	}

}
