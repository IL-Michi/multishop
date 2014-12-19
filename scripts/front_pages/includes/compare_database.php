<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$messages=array();
$skipMultishopUpdates=0;
// custom hook that can be controlled by third-party plugin
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/compare_database.php']['compareDatabasePreHook'])) {
	$params=array(
		'messages'=>&$messages,
		'skipMultishopUpdates'=>&$skipMultishopUpdates
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/compare_database.php']['compareDatabasePreHook'] as $funcRef) {
		t3lib_div::callUserFunction($funcRef, $params, $this);
	}
}
// custom hook that can be controlled by third-party plugin eof
if (!$skipMultishopUpdates) {
	// V1/V2 COMPARE DATABASE FIRST
	require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/compare_database_old.php');
	// V3 COMPARE DATABASE
	$str="select id from tx_multishop_sessions limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="CREATE TABLE `tx_multishop_sessions` (
		  `id` int(11) auto_increment,
		  `customer_id` int(11) default '0',
		  `crdate` int(11) default '0',
		  `session_id` varchar(150) default '',
		  `page_uid` int(11) default '0',
		  `ip_address` varchar(150) default '',
		  `http_host` varchar(150) default '',
		  `query_string` text,
		  `http_user_agent` text,
		  `http_referer` text,
		  `url` text,
		  `segment_type` varchar(50) default '',
		  `segment_id` varchar(50) default '',
		  PRIMARY KEY (`id`),
		  KEY `customer_id` (`customer_id`),
		  KEY `crdate` (`crdate`),
		  KEY `page_uid` (`page_uid`),
		  KEY `session_id` (`session_id`),
		  KEY `ip_address` (`ip_address`),
		  KEY `http_host` (`http_host`),
		  KEY `segment_type` (`segment_type`),
		  KEY `segment_id` (`segment_id`)
		);";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="select icecat_mid from tx_multishop_manufacturers limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE `tx_multishop_manufacturers` DROP `icecat_mid`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="select coupon_code from tx_multishop_orders limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE `tx_multishop_orders` ADD `coupon_code` varchar(255) default '', ADD KEY `coupon_code` (`coupon_code`)";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
		$str="ALTER TABLE `tx_multishop_orders` ADD `coupon_discount_type` varchar(25) default 'percentage', ADD KEY `coupon_discount_type` (`coupon_discount_type`)";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
		$str="ALTER TABLE `tx_multishop_orders` ADD `coupon_discount_value` decimal(24,14) default '0.00000000000000', ADD KEY `coupon_discount_value` (`coupon_discount_value`)";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="SHOW COLUMNS FROM `tx_multishop_customers_groups_method_mappings` WHERE Field='id' and Extra like 'AUTO%'";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
		$str="ALTER TABLE  `tx_multishop_customers_groups_method_mappings` CHANGE  `id`  `id` INT( 11 ) NOT NULL AUTO_INCREMENT";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	// attributes values image
	$str="select products_options_values_image from tx_multishop_products_options_values_to_products_options limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE `tx_multishop_products_options_values_to_products_options` ADD `products_options_values_image` varchar(255) default '', ADD KEY `products_options_values_image` (`products_options_values_image`)";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="select id from tx_multishop_invoices_export limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="CREATE TABLE `tx_multishop_invoices_export` (
			  `id` int(11) NULL AUTO_INCREMENT,
			  `name` varchar(75) NULL,
			  `page_uid` int(11) NULL DEFAULT '0',
			  `crdate` int(11) NULL DEFAULT '0',
			  `fields` text NULL,
			  `post_data` text NULL,
			  `code` varchar(150) NULL,
			  `status` tinyint(1) NULL DEFAULT '0',
			  PRIMARY KEY (`id`),
			  KEY `code` (`code`)
			);";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	// V3 COMPARE DATABASE EOL
	// V4 BETA COMPARE DATABASE (MULTIPLE SHOPS DATABASE DESIGN)
	$str="select page_uid from tx_multishop_products_to_categories limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE `tx_multishop_products_to_categories` ADD `page_uid` int(11) UNSIGNED default '0', ADD KEY `page_uid` (`page_uid`)";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
		$str="UPDATE tx_multishop_products_to_categories t1, tx_multishop_categories t2 SET t1.page_uid = t2.page_uid WHERE t1.categories_id=t2.categories_id";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="select id from tx_multishop_categories_to_categories limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="CREATE TABLE `tx_multishop_categories_to_categories` (
		  `id` int(11) auto_increment,
		  `categories_id` int(11) default '0',
		  `foreign_categories_id` int(11) default '0',
		  `page_uid` int(11) default '0',
		  `foreign_page_uid` int(11) default '0',
		  PRIMARY KEY (`id`),
		  KEY `categories_id` (`categories_id`),
		  KEY `foreign_categories_id` (`foreign_categories_id`),
		  KEY `page_uid` (`page_uid`),
		  KEY `foreign_page_uid` (`foreign_page_uid`)
		);";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="select layered_categories_id from tx_multishop_products_description limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE `tx_multishop_products_description` ADD `layered_categories_id` INT(11) NOT NULL DEFAULT '0', ADD INDEX (`layered_categories_id`);";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="select page_uid from tx_multishop_products_description limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE `tx_multishop_products_description` ADD `page_uid` INT(11) NOT NULL DEFAULT '0', ADD INDEX (`page_uid`);";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="select post_data from tx_multishop_orders_export limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE  `tx_multishop_orders_export` ADD `post_data` text default ''";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="show indexes from `tx_multishop_products_description` where Key_name='PRIMARY'";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)==2) {
		$str="ALTER TABLE tx_multishop_products_description DROP PRIMARY KEY";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$str="ALTER TABLE `tx_multishop_products_description` ADD PRIMARY KEY (`products_id`,`language_id`,`page_uid`,`layered_categories_id`)";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="select payment_condition from tx_multishop_orders";
	if (!$qry=$GLOBALS['TYPO3_DB']->sql_query($str)) {
		$str="ALTER TABLE `tx_multishop_orders` ADD `payment_condition` varchar(50) NULL DEFAULT '', ADD INDEX (`payment_condition`);";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="select tx_multishop_payment_condition from fe_users";
	if (!$qry=$GLOBALS['TYPO3_DB']->sql_query($str)) {
		$str="ALTER TABLE `fe_users` ADD `tx_multishop_payment_condition` varchar(50) NULL DEFAULT '', ADD INDEX (`tx_multishop_payment_condition`);";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}

	$str="select related_to from tx_multishop_products_to_categories limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE  `tx_multishop_products_to_categories` ADD `related_to` INT(11) NOT NULL DEFAULT '0', ADD INDEX (`related_to`);";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="select page_uid from tx_multishop_products_to_categories where page_uid='0' limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if ($qry && $this->showCatalogFromPage && $GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
		$str="UPDATE tx_multishop_products_to_categories t1, tx_multishop_categories t2 SET t1.page_uid = t2.page_uid WHERE t1.page_uid=0 AND t1.categories_id=t2.categories_id";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
		/*
		$str="UPDATE `tx_multishop_products_to_categories` SET page_uid='".$this->showCatalogFromPage."' where page_uid='0'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
		*/
	}
	$str="select page_uid from tx_multishop_products_description where page_uid='0' limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if ($qry && $this->showCatalogFromPage && $GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
		$str="UPDATE tx_multishop_products_description t1, tx_multishop_products t2 SET t1.page_uid = t2.page_uid WHERE t1.page_uid=0 AND t1.products_id=t2.products_id";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
		/*
		$str="UPDATE `tx_multishop_products_description` SET page_uid='".$this->showCatalogFromPage."' where page_uid='0'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
		*/
	}
	$str="select id from tx_multishop_product_crop_image_coordinate limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="CREATE TABLE `tx_multishop_product_crop_image_coordinate` (
		  `id` int(11) auto_increment,
		  `products_id` int(11) default '0',
		  `image_filename` varchar(255) default '',
		  `image_size` varchar(10) DEFAULT '',
		  `coordinate_x` int(11) default '0',
		  `coordinate_y` int(11) default '0',
		  `coordinate_w` int(11) default '0',
		  `coordinate_h` int(11) default '0',
		  PRIMARY KEY (`id`),
		  KEY `products_id` (`products_id`),
		  KEY `image_filename` (`image_filename`),
		  KEY `image_size` (`image_size`),
		  KEY `coordinate_x` (`coordinate_x`),
		  KEY `coordinate_y` (`coordinate_y`),
		  KEY `coordinate_w` (`coordinate_w`),
		  KEY `coordinate_h` (`coordinate_h`)
		);";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="select id from tx_multishop_categories_crop_image_coordinate limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="CREATE TABLE `tx_multishop_categories_crop_image_coordinate` (
		  `id` int(11) auto_increment,
		  `categories_id` int(11) default '0',
		  `image_filename` varchar(255) default '',
		  `image_size` varchar(10) DEFAULT '',
		  `coordinate_x` int(11) default '0',
		  `coordinate_y` int(11) default '0',
		  `coordinate_w` int(11) default '0',
		  `coordinate_h` int(11) default '0',
		  PRIMARY KEY (`id`),
		  KEY `categories_id` (`categories_id`),
		  KEY `image_filename` (`image_filename`),
		  KEY `image_size` (`image_size`),
		  KEY `coordinate_x` (`coordinate_x`),
		  KEY `coordinate_y` (`coordinate_y`),
		  KEY `coordinate_w` (`coordinate_w`),
		  KEY `coordinate_h` (`coordinate_h`)
		);";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="select id from tx_multishop_manufacturers_crop_image_coordinate limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="CREATE TABLE `tx_multishop_manufacturers_crop_image_coordinate` (
		  `id` int(11) auto_increment,
		  `manufacturers_id` int(11) default '0',
		  `image_filename` varchar(255) default '',
		  `image_size` varchar(10) DEFAULT '',
		  `coordinate_x` int(11) default '0',
		  `coordinate_y` int(11) default '0',
		  `coordinate_w` int(11) default '0',
		  `coordinate_h` int(11) default '0',
		  PRIMARY KEY (`id`),
		  KEY `manufacturers_id` (`manufacturers_id`),
		  KEY `image_filename` (`image_filename`),
		  KEY `image_size` (`image_size`),
		  KEY `coordinate_x` (`coordinate_x`),
		  KEY `coordinate_y` (`coordinate_y`),
		  KEY `coordinate_w` (`coordinate_w`),
		  KEY `coordinate_h` (`coordinate_h`)
		);";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	// cms table
	$str="describe `tx_multishop_cms`";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		if ($row['Field']=='id') {
			if ($row['Type']=='int(3)') {
				$str2="ALTER TABLE  `tx_multishop_cms` CHANGE  `id`  `id` int(11)";
				$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
				$messages[]=$str2;
			}
		}
	}
	// cms table
	$str="describe `tx_multishop_cms_description`";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		if ($row['Field']=='id') {
			if ($row['Type']=='int(3)') {
				$str2="ALTER TABLE  `tx_multishop_cms_description` CHANGE  `id`  `id` int(11)";
				$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
				$messages[]=$str2;
			}
			if ($row['Key']!='PRI') {
				$str2="ALTER TABLE  `tx_multishop_cms_description` ADD PRIMARY KEY  (`id`, `language_id`)";
				$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
				$messages[]=$str2;
			}
		}
	}
	// TYPO3 6 - NULL VALUES BUGFIX
	$items=array();
	$items[]=array('table'=>'tx_multishop_products_description','column'=>'products_meta_title','columnDefinition'=>'varchar(254)','allowNull'=>1);
	$items[]=array('table'=>'tx_multishop_products_description','column'=>'products_meta_description','columnDefinition'=>'varchar(254)','allowNull'=>1);
	foreach ($items as $item) {
		$str="describe `".$item['table']."`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']==$item['column']) {
				if ($row['Null']=='NO' && $item['allowNull']) {
					$str2="ALTER TABLE  `".$item['table']."` CHANGE `".$item['column']."` `".$item['column']."` ".$item['columnDefinition']." default NULL";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
		}
	}
	// TYPO3 6 - NULL VALUES BUGFIX EOL
	$str="select relation_types from tx_multishop_products_to_relative_products limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE `tx_multishop_products_to_relative_products` ADD `relation_types` varchar(15) NOT NULL DEFAULT 'cross-sell', ADD INDEX (`relation_types`);";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	// add sort_order columns to orders_products
	$str="select sort_order from tx_multishop_orders_products limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE `tx_multishop_orders_products` ADD `sort_order` int(11) NOT NULL DEFAULT '0', ADD INDEX (`sort_order`)";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
		// initiate the first value based on orders_products_id
		$str="update tx_multishop_orders_products set sort_order=orders_products_id";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	// add sort_order columns to shipping methods to zones
	$str="select sort_order from tx_multishop_shipping_methods_to_zones limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE `tx_multishop_shipping_methods_to_zones` ADD `sort_order` int(11) NOT NULL DEFAULT '0', ADD INDEX (`sort_order`)";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	// add sort_order columns to payment methods to zones
	$str="select sort_order from tx_multishop_payment_methods_to_zones limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE `tx_multishop_payment_methods_to_zones` ADD `sort_order` int(11) NOT NULL DEFAULT '0', ADD INDEX (`sort_order`)";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	// add manufacturers_advice_price columns to products tabel
	$str="select manufacturers_advice_price from tx_multishop_products limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE `tx_multishop_products` ADD `manufacturers_advice_price` decimal(24,14) NOT NULL DEFAULT '0.00000000000000', ADD INDEX (`manufacturers_advice_price`)";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	/*
	// V4 BETA COMPARE DATABASE (MULTIPLE SHOPS DATABASE DESIGN) EOL
	$str="select tx_multishop_customer_id from fe_users limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE `fe_users` ADD `tx_multishop_customer_id` int(11) UNSIGNED default '0', ADD KEY `tx_multishop_customer_id` (`tx_multishop_customer_id`)";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
		$str="UPDATE `fe_users` set tx_multishop_customer_id=uid where tx_multishop_customer_id='0'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	*/
	// V4 BETA COMPARE DATABASE (MULTIPLE SHOPS DATABASE DESIGN) EOL
	// CREATE / UPDATE MULTISHOP SETTINGS. CAN BE FURTHER CONTROLLED BY THIRD PARTY PLUGINS.
	require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/configuration/tx_multishop_configuration_group.php');
	foreach ($records as $record) {
		if (!mslib_befe::ifExists($record['id'], 'tx_multishop_configuration_group', 'id')) {
			$query2=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_configuration_group', $record);
			$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
			$messages[]=$query2;
		}
	}
	require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/configuration/tx_multishop_configuration.php');
	foreach ($records as $record) {
		if (!mslib_befe::ifExists($record['configuration_key'], 'tx_multishop_configuration', 'configuration_key')) {
			$query2=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_configuration', $record);
			$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
			$messages[]=$query2;
		}
	}
	// CREATE / UPDATE MULTISHOP SETTINGS. CAN BE FURTHER CONTROLLED BY THIRD PARTY PLUGINS. EOL
}
?>