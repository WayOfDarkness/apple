<?php

require('../vendor/autoload.php');
require('../framework/config.php');

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\DatabaseManager as DB;



$capsule = new Capsule;
$capsule->addConnection($config['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();


date_default_timezone_set('Asia/Ho_Chi_Minh');

$sqls = [
  "ALTER TABLE `product` CHANGE `inventory_management` `stock_manage` TINYINT(1) NOT NULL DEFAULT '1'",
  "ALTER TABLE `product` CHANGE `stock` `stock_quant` INT(11) NOT NULL DEFAULT '1';",
  "ALTER TABLE `variant` CHANGE `inventory` `stock_quant` INT(11) NOT NULL DEFAULT '0';",
  "ALTER TABLE `product` CHANGE `featured_image` `image` TEXT DEFAULT ''",
  "ALTER TABLE `collection` ADD COLUMN `product_tags` TEXT DEFAULT ''",
  "ALTER TABLE `product` ADD COLUMN `stop_selling` VARCHAR(255) DEFAULT 'publish'",
  "ALTER TABLE `attribute` ADD COLUMN `status` VARCHAR(255) DEFAULT 'active'",
  "ALTER TABLE `blog` ADD COLUMN `article_tags` TEXT DEFAULT ''",
  "ALTER TABLE `article` ADD COLUMN `author` TEXT DEFAULT ''",
  "ALTER TABLE `customer` ADD COLUMN `gender` VARCHAR(255) DEFAULT ''",
  "ALTER TABLE `customer` ADD COLUMN `birthday` DATE DEFAULT NULL",
  "ALTER TABLE `customer` ADD COLUMN `company` VARCHAR(255) DEFAULT ''",
  "ALTER TABLE `customer` ADD COLUMN `member_type` VARCHAR(255) DEFAULT ''",
  "ALTER TABLE `variant` ADD COLUMN `sale_id` INT(10) UNSIGNED",
  "ALTER TABLE `variant` ADD FOREIGN KEY (sale_id) REFERENCES sale(id)",
  "ALTER TABLE `subscriber` ADD COLUMN `type` VARCHAR(255) DEFAULT ''",
  "ALTER TABLE `photo` ADD COLUMN `priority` INT(11) NOT NULL DEFAULT '0';",
  "ALTER TABLE `customer` DROP COLUMN `favorite_product`",
  "ALTER TABLE `gallery` ADD COLUMN `parent_id` INT(11) DEFAULT '-1'",
  "ALTER TABLE `testimonial_translations` ADD COLUMN `name` VARCHAR(255) DEFAULT ''",
  "ALTER TABLE `menu_translations` ADD COLUMN `link` VARCHAR(255) DEFAULT ''",
  "ALTER TABLE `review` ADD COLUMN `customer_id` INT(11)",
  "ALTER TABLE `review` ADD COLUMN `status` VARCHAR(255) DEFAULT 'inactive'",
  "ALTER TABLE `article` DROP COLUMN `title_en`",
  "ALTER TABLE `article` DROP COLUMN `description_en`",
  "ALTER TABLE `article` DROP COLUMN `content_en`",
  "ALTER TABLE `article` MODIFY `publish_date` DATETIME",
  "ALTER TABLE `attribute` DROP COLUMN `name_en`",
  "ALTER TABLE `blog` DROP COLUMN `title_en`",
  "ALTER TABLE `blog` DROP COLUMN `description_en`",
  "ALTER TABLE `blog` DROP COLUMN `content_en`",
  "ALTER TABLE `collection` DROP COLUMN `title_en`",
  "ALTER TABLE `collection` DROP COLUMN `description_en`",
  "ALTER TABLE `collection` DROP COLUMN `content_en`",
  "ALTER TABLE `gallery` DROP COLUMN `title_en`",
  "ALTER TABLE `gallery` DROP COLUMN `description_en`",
  "ALTER TABLE `menu` DROP COLUMN `description_en`",
  "ALTER TABLE `page` DROP COLUMN `title_en`",
  "ALTER TABLE `page` DROP COLUMN `description_en`",
  "ALTER TABLE `page` DROP COLUMN `content_en`",
  "ALTER TABLE `photo` DROP COLUMN `title_en`",
  "ALTER TABLE `photo` DROP COLUMN `description_en`",
  "ALTER TABLE `product` DROP COLUMN `title_en`",
  "ALTER TABLE `product` DROP COLUMN `description_en`",
  "ALTER TABLE `product` DROP COLUMN `content_en`",
  "ALTER TABLE `seo` DROP COLUMN `g_meta_title_en`",
  "ALTER TABLE `seo` DROP COLUMN `g_meta_description_en`",
  "ALTER TABLE `seo` DROP COLUMN `g_meta_keyword_en`",
  "ALTER TABLE `seo` DROP COLUMN `f_meta_title_en`",
  "ALTER TABLE `seo` DROP COLUMN `f_meta_description_en`",
  "ALTER TABLE `testimonial` DROP COLUMN `content_en`",
  "ALTER TABLE `review` ADD COLUMN `customer_id` VARCHAR(255) DEFAULT '-1'",
  "ALTER TABLE `testimonial_translations` ADD COLUMN `name` VARCHAR(255) DEFAULT ''",
  "ALTER TABLE `photo` ADD COLUMN `status` VARCHAR(255) DEFAULT 'active'",
  "ALTER TABLE `collection` DROP COLUMN `breadcrumb`",
  "ALTER TABLE `variant` MODIFY `price` BIGINT",
  "ALTER TABLE `variant` MODIFY `price_compare` BIGINT",
  "ALTER TABLE `review` ADD COLUMN `like` INT(11) DEFAULT '0'",
  "ALTER TABLE `review` ADD COLUMN `dislike` INT(11) DEFAULT '0'",
  "ALTER TABLE `contact` ADD COLUMN `read` TINYINT(1) DEFAULT '0'",
  "ALTER TABLE `contact` ADD COLUMN `reply` TINYINT(1) DEFAULT '0'",
  "ALTER TABLE `contact` ADD COLUMN `status` VARCHAR(255) DEFAULT 'active'",
  "ALTER TABLE `contact` DROP COLUMN `read_status`",
  "ALTER TABLE `contact` DROP COLUMN `reply_status`",
  "ALTER TABLE `contact` DROP COLUMN `display_status`",
  "ALTER TABLE `customer` ADD COLUMN `avatar` TEXT DEFAULT ''",
  "ALTER TABLE `sale` ADD COLUMN `type_relation` TEXT DEFAULT 'product'",
  "ALTER TABLE `collection_product` ADD COLUMN `priority` INT(11) DEFAULT '1000'",
  "ALTER TABLE `blog_article` ADD COLUMN `priority` INT(11) DEFAULT '1000'",
  "ALTER TABLE `seo` CHANGE `g_meta_title` `meta_title` TEXT DEFAULT ''",
  "ALTER TABLE `seo` CHANGE `g_meta_description` `meta_description` TEXT DEFAULT ''",
  "ALTER TABLE `seo` CHANGE `g_meta_keyword` `meta_keyword` TEXT DEFAULT ''",
  "ALTER TABLE `seo` CHANGE `g_meta_robots` `meta_robots` TEXT DEFAULT ''",
  "ALTER TABLE `seo` CHANGE `f_image` `meta_image` TEXT DEFAULT ''",
  "ALTER TABLE `seo` DROP COLUMN `f_meta_title`",
  "ALTER TABLE `seo` DROP COLUMN `f_meta_description`",
  "ALTER TABLE `seo_translations` CHANGE `g_meta_title` `meta_title` TEXT DEFAULT ''",
  "ALTER TABLE `seo_translations` CHANGE `g_meta_description` `meta_description` TEXT DEFAULT ''",
  "ALTER TABLE `seo_translations` CHANGE `g_meta_keyword` `meta_keyword` TEXT DEFAULT ''",
  "ALTER TABLE `seo_translations` DROP COLUMN `f_meta_title`",
  "ALTER TABLE `seo_translations` DROP COLUMN `f_meta_description`",
  "ALTER TABLE `sale_product` CHANGE `product_id` `type_id` INT(11) DEFAULT '0'",
  "ALTER TABLE `order` ADD COLUMN `sale` TEXT DEFAULT ''",
  "ALTER TABLE `order` ADD COLUMN `sale_discount` INT(11) DEFAULT '0'",
  "ALTER TABLE `variant` DROP COLUMN `check`",
  "ALTER TABLE `coupon` DROP COLUMN `title_en`",
  "ALTER TABLE `coupon` DROP COLUMN `description_en`",
  "ALTER TABLE `customer` ADD COLUMN `username` TEXT DEFAULT ''",
  "ALTER TABLE `product` ADD COLUMN `rating` FLOAT DEFAULT 0",

  "ALTER TABLE `order` ADD COLUMN `discount_point` INT(11) DEFAULT 0",
  "ALTER TABLE `order` ADD COLUMN `use_point` INT(11) DEFAULT 0",
  "ALTER TABLE `customer` ADD COLUMN `ref_id` INT(11) DEFAULT 0",
  "ALTER TABLE `customer` AUTO_INCREMENT = 100000",
  "ALTER TABLE `product` MODIFY `option_1` VARCHAR(255) DEFAULT ''",
  "ALTER TABLE `product` MODIFY `option_2` VARCHAR(255) DEFAULT ''",
  "ALTER TABLE `product` MODIFY `option_3` VARCHAR(255) DEFAULT ''",
  "ALTER TABLE `product` MODIFY `option_4` VARCHAR(255) DEFAULT ''",
  "ALTER TABLE `product` MODIFY `option_5` VARCHAR(255) DEFAULT ''",
  "ALTER TABLE `product` MODIFY `option_6` VARCHAR(255) DEFAULT ''",
  "ALTER TABLE `product` MODIFY `stop_selling` VARCHAR(255) DEFAULT 'publish'",

  "ALTER TABLE `product` ADD COLUMN `raw_title` text",
  "ALTER TABLE `product` ADD COLUMN `raw_full` text",
  "ALTER TABLE `article` ADD COLUMN `raw_text` text",
  'ALTER TABLE `article` ADD FULLTEXT (raw_text)',
  'ALTER TABLE `product` ADD FULLTEXT (raw_title)',
  'ALTER TABLE `product` ADD FULLTEXT (raw_full)',
  "ALTER TABLE `blog` ADD COLUMN `game_id` INT(11) DEFAULT '0'",
  "ALTER TABLE `article` ADD COLUMN `game_id` INT(11) DEFAULT '0'",
  "ALTER TABLE `article` ADD COLUMN `admin_point` INT(11) DEFAULT '0'",
  "ALTER TABLE `article` ADD COLUMN `user_point` INT(11) DEFAULT '0'",
  "ALTER TABLE `article` ADD COLUMN `type` VARCHAR(255) DEFAULT 'news'",
  "ALTER TABLE `article` ADD COLUMN `option_1` VARCHAR(255) DEFAULT ''",
  "ALTER TABLE `article` ADD COLUMN `option_2` VARCHAR(255) DEFAULT ''",
  "ALTER TABLE `article` ADD COLUMN `option_3` VARCHAR(255) DEFAULT ''",
  "ALTER TABLE `article` ADD COLUMN `option_4` VARCHAR(255) DEFAULT ''",
  "ALTER TABLE `article` ADD COLUMN `option_5` VARCHAR(255) DEFAULT ''",
  "ALTER TABLE `article` ADD COLUMN `option_6` VARCHAR(255) DEFAULT ''",
  "ALTER TABLE `article` ADD COLUMN `like` INT(11) DEFAULT 0",
  "ALTER TABLE `article` ADD COLUMN `dislike` INT(11) DEFAULT 0",
  "ALTER TABLE `customer_review` ADD COLUMN `post_type` VARCHAR(255) DEFAULT ''",
  "ALTER TABLE `game` ADD COLUMN `infomation` text",
  "ALTER TABLE `customer` ADD COLUMN `point` INT(11) DEFAULT 0",
];


foreach($sqls as $sql) {
    echo "SQL: $sql \n";
    try {
        echo Capsule::statement($sql);
    } catch (Exception $e) {
        echo $e->getMessage();
    }

    echo "\n---\n";
}

$arrSubRegion = [
  [697,'Huyện Bảo Lâm','166'],
  [698,'Huyện Châu Thành','179'],
  [699,'Huyện Châu Thành','175'],
  [700,'Huyện Phú Tân','185'],
  [701,'Huyện Vĩnh Thạnh','181'],
  [702,'Huyện Phong Điền','181'],
  [703,'Huyện Châu Thành','178'],
  [704,'Huyện Tam Nông','178'],
  [705,'Quận Ba Đình','123'],
  [706,'Huyện Châu Thành','182'],
  [707,'Huyện Châu Thành','180'],
  [708,'Huyện Châu Thành','173'],
  [709,'Huyện Kỳ Sơn','149'],
  [710,'Huyện Châu Thành','183'],
  [711,'Huyện Châu Thành','174'],
  [712,'Huyện Châu Thành','176']
];


foreach ($arrSubRegion as $subRegion){
  Capsule::insert('INSERT INTO ' . Capsule::getTablePrefix()
    . 'subregion (id, `name`, region_id)'
    . 'VALUES (?, ?, ?)',
    $subRegion
  );

}
