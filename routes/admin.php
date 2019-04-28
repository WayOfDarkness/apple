<?php

use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Mailgun\Assert;
use \Firebase\JWT\JWT;

require_once("../models/helper.php");
require_once("../models/User.php");
require_once("../models/Branch.php");
require_once("../framework/auth.php");
$adminDir = ROOT . '/controllers/admin/';

$files = scandir($adminDir);
$files = array_diff($files, array('.', '..', __FILE__));
foreach ($files as $file) {
  require_once($adminDir . $file);
}


$role = $_SESSION['role'];

$app->add(new JwtAndSessionAuth());
$app->get('/show-jwt', function ($req, $res) {
  return $res->write(
    json_encode($req->getAttributes())
  );
});


$app->get('/api/me', function ($req, $res) {
  $cookie = $req->getCookieParams();
  $data = [];
  $data ['cookie'] = $cookie;
  $data ['headers'] = $req->getHeaders();

  if (isset($cookie['jwt'])) {
    $attrs = JwtAndSessionAuth::decodeJwt($cookie['jwt']);
    $data ['auth'] = $attrs;
  }

  return $res->withJson($data);
});

$app->get('/admin/create_password/{random}', '\AdminUserController:createPassword');

$app->get("/admin", function ($req, $res) {
  if (isset($_SESSION['href'])) return $res->withStatus(302)->withHeader('Location', $_SESSION['href']);
  return $res->withStatus(302)->withHeader('Location', '/admin/login');
});

$app->get('/admin/login', '\AdminUserController:getlogin');
$app->get('/admin/logout', '\AdminUserController:getLogout');
$app->get('/admin/403', '\AdminUserController:error');
$app->get('/admin/api/attributes', '\AdminAttributeController:list');
$app->get('/admin/api/customfield/define', 'getCustomFieldDefine');
$app->get('/admin/api/view/settingViewAPI', '\AdminSettingController:settingViewAPI');
$app->get('/admin/api/dashboard', '\AdminDashboardController:list');
$app->post('/admin/api/import_product', '\AdminProductController:importProduct');
$app->post('/admin/api/import_order', '\AdminOrderController:importOrder');
$app->post('/admin/api/uploadProductImage', '\AdminProductController:uploadProductImage');

$app->group('/admin', function () use ($app) {

  if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false && substr($_SERVER['REQUEST_URI'], -1) == '/') {
    $url = substr($_SERVER['REQUEST_URI'], 0, -1);
    if (strlen($url) <= strlen("/admin/")) {
      header('Location: /admin/dashboard');
      exit();
    }
  }

  $app->get("/", function ($req, $res) {
    $href = $_SESSION['href'];
    if (!isset($_SESSION['href']) || $_SESSION['href'] == '/admin/') {
      return $res->withStatus(302)->withHeader('Location', '/admin/dashboard');
    }
    return $res->withStatus(302)->withHeader('Location', $href);
  });

  $app->get('/dashboard', '\AdminDashboardController:fetch');

  //order *
  $app->get('/order', '\AdminOrderController:index');
  $app->get('/order/search', '\AdminOrderController:search');
  $app->get('/order/create', '\AdminOrderController:create');
  $app->get('/order/{id}', '\AdminOrderController:show');
  $app->put('/order/{id}', '\AdminOrderController:update');
  $app->post('/api/order/updateStatusOrder','\AdminOrderController:updateStatusOrder');
  $app->post('/api/order/updateStatusPayment','\AdminOrderController:updateStatusPayment');
  $app->put('/api/order/{id}/discount','\AdminOrderController:updateDiscount');
  $app->put('/api/order/{id}/shipping_fee','\AdminOrderController:updateShippingFee');
  $app->put('/api/order/{id}/addCoupon','\AdminOrderController:addCoupon');
  $app->get('/api/order/loadProduct','\AdminOrderController:loadProduct');
  $app->get('/api/order/loadSubregion','\AdminOrderController:loadSubregion');
  $app->get('/api/order/addProduct','\AdminOrderController:addProduct');
  $app->get('/api/order/loadInfo','\AdminOrderController:loadInfo');
  $app->put('/api/updateShipping/{id}','\AdminOrderController:updateShipping');
  $app->get('/api/order/status', '\AdminOrderController:status');
  $app->get('/remove-order', '\AdminOrderController:removeOrder');

  // giao hang tiet kiem shipping
  $app->get('/api/order/getpriceghtk/{id}', '\AdminOrderController:GetShippingFee'); //shipping charge
  $app->get('/api/order/setpriceghtk/{id}', '\AdminOrderController:setOrderShipping'); //post order
  $app->get('/api/order/cancelghtk/{id}', '\AdminOrderController:cancelShipping'); // Cancle order
  $app->get('/api/order/getInfoOrder/{id}', '\AdminOrderController:getInfoOrder'); // Get information order
  $app->post('/api/addWebhooksUrl', '\AdminOrderController:addWebhooksUrl'); // Get information order
  $app->post('/api/removeWebhooksUrl', '\AdminOrderController:removeWebhooksUrl'); // Get information Webhooks
  $app->get('/api/listWebhooksUrl', '\AdminOrderController:listWebhooksUrl'); // Get information Webhooks

  //author
  $app->get('/api/author','\AdminAuthorController:getAuthor');
  //tag *
  $app->get('/tag','\AdminTagController:get');
  $app->get('/api/tag','\AdminTagController:getTag');
  $app->post('/api/product/tag', '\AdminProductController:addProductTag');
  $app->put('/api/product/tag', '\AdminTagController:update');
  $app->delete('/api/product/tag', '\AdminProductController:deleteTag');

  //product buy together *
  $app->get('/product_buy_together', '\AdminProductBuyTogetherController:fetch');
  $app->post('/product_buy_together', '\AdminProductBuyTogetherController:store');
  $app->post('/product_buy_together/one', '\AdminProductBuyTogetherController:storeOne');
  $app->get('/product_buy_together/create', '\AdminProductBuyTogetherController:create');
  $app->get('/product_buy_together/{id}', '\AdminProductBuyTogetherController:show');
  $app->put('/product_buy_together/{id}', '\AdminProductBuyTogetherController:update');
  $app->delete('/product_buy_together/{id}', '\AdminProductBuyTogetherController:delete');
  $app->get('/api/product/detail/{id}', '\AdminProductController:getDetail');

  //import product *
  $app->get('/import_product', '\AdminProductController:viewImportProduct');
  $app->get('/import_product/template', '\AdminProductController:rednerExcelTemplate');
  $app->post('/import_product', '\AdminProductController:importProduct');
  $app->post('/api/exportExcel', '\AdminExportController:exportExcel');
  $app->post('/api/exportProductExcel', '\AdminProductController:exportProductExcel');

  //coupon *
  $app->get('/coupon', '\AdminCouponController:fetch');
  $app->get('/coupon/create', '\AdminCouponController:create');
  $app->post('/coupon', '\AdminCouponController:store');
  $app->get('/coupon/{id}', '\AdminCouponController:get');
  $app->put('/coupon/{id}', '\AdminCouponController:update');
  $app->delete('/coupon/{id}', '\AdminCouponController:delete');

  $app->post('/metafield', '\AdminMetafieldController:store');
  $app->post('/api/db_metafield', '\AdminMetafieldController:storeDBMetafield');

  //sale *
  $app->get('/sale', '\AdminSaleController:fetch');
  $app->post('/sale', '\AdminSaleController:store');
  $app->get('/sale/create', '\AdminSaleController:create');
  $app->get('/sale/{id}', '\AdminSaleController:get');
  $app->put('/sale/{id}', '\AdminSaleController:update');
  $app->delete('/sale/{id}', '\AdminSaleController:delete');

  //variant *
  $app->get('/api/variants', '\AdminVariantController:list');
  $app->get('/variant/{id}', '\AdminVariantController:get');
  $app->post('/variant', '\AdminVariantController:store');
  $app->put('/variant/{id}', '\AdminVariantController:update');
  $app->put('/variant', '\AdminVariantController:updateMore');
  $app->delete('/variant/{id}', '\AdminVariantController:delete');

  //collection *
  $app->get('/collection', '\AdminCollectionController:index');
  $app->get('/collection/create', '\AdminCollectionController:create');
  $app->get('/collection/{id}', '\AdminCollectionController:get');
  $app->post('/collection', '\AdminCollectionController:store');
  $app->put('/collection/{id}', '\AdminCollectionController:update');
  $app->delete('/collection/{id}', '\AdminCollectionController:delete');
  $app->post('/api/collection/sortProduct/{id}', '\AdminCollectionController:sortProduct');
  $app->delete('/api/collection/removeProduct', '\AdminCollectionController:removeProduct');
  $app->post('/api/collection/addMuch', '\AdminCollectionController:addMuch');
  $app->delete('/api/collection/deleteMuch', '\AdminCollectionController:deleteMuch');
  $app->post('/api/collection/deleteMuch', '\AdminCollectionController:deleteMuch');
  $app->put('/api/productCollection/updatePriority', '\AdminCollectionController:updatePriority');

  //attribute *
  $app->get('/attribute', '\AdminAttributeController:index');
  $app->post('/attribute', '\AdminAttributeController:store');
  $app->get('/attribute/{id}', '\AdminAttributeController:get');
  $app->put('/attribute/{id}', '\AdminAttributeController:update');
  $app->delete('/attribute/{id}', '\AdminAttributeController:delete');

  //price
  $app->get('/price', '\AdminPriceController:index');
  $app->post('/price', '\AdminPriceController:store');
  $app->put('/price/{id}', '\AdminPriceController:update');
  $app->delete('/price/{id}', '\AdminPriceController:delete');

  //Customer *
  $app->get('/customer', '\AdminCustomerController:fetch');
  $app->get('/customer/order/{id}', '\AdminCustomerController:showOrder');
  $app->get('/customer/export', '\AdminCustomerController:export');
  $app->get('/customer/{id}', '\AdminCustomerController:get');
  $app->put('/customer/{id}', '\AdminCustomerController:update');

  //comment *
  $app->get('/comment', '\AdminCommentController:fetch');
  $app->put('/comment/{id}', '\AdminCommentController:update');
  $app->delete('/comment/{id}', '\AdminCommentController:delete');

  //testimonial *
  $app->get('/testimonial', '\AdminTestimonialController:fetch');
  $app->get('/testimonial/create', '\AdminTestimonialController:create');
  $app->get('/testimonial/{id}', '\AdminTestimonialController:get');
  $app->post('/testimonial', '\AdminTestimonialController:store');
  $app->put('/testimonial/{id}', '\AdminTestimonialController:update');

  //client *
  $app->get('/client', '\AdminClientController:fetch');
  $app->get('/client/create', '\AdminClientController:create');
  $app->get('/client/{id}', '\AdminClientController:get');
  $app->post('/client', '\AdminClientController:store');
  $app->put('/client/{id}', '\AdminClientController:update');

  //contact *
  $app->get('/contact', '\AdminContactController:fetch');
  $app->get('/contact/{id}', '\AdminContactController:getContact');
  $app->post('/contact', '\AdminContactController:store');
  $app->put('/contact/updateStatus', '\AdminContactController:updateStatus');
  $app->delete('/contact/{id}', '\AdminContactController:delete');
  $app->get('/api/contact/status', '\AdminContactController:status');

  //review
  $app->get('/review', '\AdminReviewController:fetch');
  $app->get('/review/{id}', '\AdminReviewController:get');
  $app->put('/review/{id}', '\AdminReviewController:update');

  //subscriber
  $app->get('/subscriber', '\AdminCustomerController:subscriber');

  //gallery
  $app->get('/gallery', '\AdminGalleryController:fetch');
  $app->get('/gallery/create', '\AdminGalleryController:create');
  $app->get('/gallery/{id}', '\AdminGalleryController:get');
  $app->post('/gallery', '\AdminGalleryController:store');
  $app->put('/gallery/{id}', '\AdminGalleryController:update');
  $app->post('/gallery/double', '\AdminGalleryController:double');
  $app->put('/api/gallery/updatePriority', '\AdminGalleryController:updatePriority');

  //file
  $app->get('/file', '\AdminFileController:fetch');

  //photo
  $app->get('/photo/create', '\AdminPhotoController:create');
  $app->get('/photo/{id}', '\AdminPhotoController:get');
  $app->post('/photo', '\AdminPhotoController:store');
  $app->put('/photo/{id}', '\AdminPhotoController:update');
  $app->delete('/photo/{id}', '\AdminPhotoController:delete');

  //library
  $app->get('/library', '\AdminLibraryController:fetch');

  //shipping *
  $app->get('/shipping_fee', '\AdminShippingFeeController:fetch');
  $app->get('/shipping_fee/create', '\AdminShippingFeeController:create');
  $app->post('/shipping_fee', '\AdminShippingFeeController:store');
  $app->get('/shipping_fee/edit/{regionid}', '\AdminShippingFeeController:edit');
  $app->put('/shipping_fee/{id}', '\AdminShippingFeeController:update');
  $app->delete('/shipping_fee/{id}', '\AdminShippingFeeController:delete');
  $app->get('/api/shipping_fee/{id}', '\AdminShippingFeeController:loaddata');
  $app->get('/api/shipping_fee/fee/{id}', '\AdminShippingFeeController:shippingfee');
  $app->get('/api/shipping_fee/feeAPI/{id}', '\AdminShippingFeeController:shippingfeeAPI');
  $app->get('/api/subregion/{id}', '\AdminSubregionController:subRegion');

  //menu *
  $app->get('/menu', '\AdminMenuController:index');
  $app->get('/menu/create', '\AdminMenuController:create');
  $app->get('/menu/{id}', '\AdminMenuController:get');
  $app->post('/menu', '\AdminMenuController:store');
  $app->put('/menu/updatePriority', '\AdminMenuController:updatePriority');
  $app->put('/menu/{id}', '\AdminMenuController:update');
  $app->delete('/menu/{id}', '\AdminMenuController:delete');
  $app->delete('/menu', '\AdminMenuController:deleteArr');
  $app->get('/api/menu/{id}/detail', '\AdminMenuController:detail');
  $app->get('/api/menu/{id}', '\AdminMenuController:getMenu');

  //setting *
  $app->get('/setting', '\AdminSettingController:setting');
  $app->put('/setting', '\AdminSettingController:updateSetting');
  $app->get('/api/setting/getList', '\AdminSettingController:getList');
  $app->get('/api/link/{type}', '\AdminSettingController:getLinkType');
  $app->get('/api/setting/version', '\AdminSettingController:getVersion');
  $app->get('/api/setting/{key}', '\AdminSettingController:getValue');
  $app->get('/api/loadSetting', '\AdminSettingController:loadSetting');

  //super admin setting
  $app->get('/user/setting', '\AdminUserController:setting');
  $app->get('/api/view/settingAdmin', '\AdminUserController:settingViewAdmin');
  $app->put('/superAdminSetting', '\SuperAdminSettingController:updateSuperAdminSetting');
  $app->get('/api/superAdminSetting/version', '\SuperAdminSettingController:getVersion');
  $app->get('/api/getSuperAdminSetting', '\SuperAdminSettingController:getSuperAdminSetting');
  $app->get('/api/loadSuperadminSetting', '\SuperAdminSettingController:loadSetting');

  //user *
  $app->get('/user/history', '\AdminUserController:history');
  $app->put('/api/user/changePassword', '\AdminUserController:changePassword');
  $app->get('/api/resendEmail/user/{random}','\AdminUserController:resendEmail');

  //api image
  $app->post('/api/uploadImage/{name}', 'uploadImage');
  $app->post('/api/downloadImage', 'downloadImage');
  $app->post('/api/createFolder', 'createFolder');
  $app->post('/api/removeFolder', 'removeFolder');
  $app->post('/api/moveImages', 'moveImages');
  $app->post('/api/getUpload', 'getUpload');

  //api file
  $app->post('/api/uploadFile', 'uploadFile');
  //api restore
  $app->post('/api/restore', 'restoreStatus');

  //image
  $app->get('/api/uploads', '\AdminSettingController:getUploads');
  $app->post('/api/removeUploads', '\AdminSettingController:removeImageUploads');
  $app->post('/api/updateStatus', 'updateStatus');
  $app->get('/api/rotate', 'rotateImage');

  $app->get('/api/checkPostHandle', 'checkPostHandle');
  $app->post('/api/seo', '\AdminProductController:Seo');
  $app->get('/api/getChildAttribute', '\AdminAttributeController:getChildAttribute');
  $app->get('/api/permission', '\AdminRoleController:Permission');
  $app->get('/api/get_url', 'get_url');

  // blog
  $app->post('/api/blog/addMultipleBlog', '\AdminBlogController:addMultipleBlog');
  $app->delete('/api/blog/deleteMultipleBlog', '\AdminBlogController:deleteMultipleBlog');
  $app->post('/api/blog/deleteMultipleBlog', '\AdminBlogController:deleteMultipleBlog');
  $app->delete('/api/blog/removeArticle', '\AdminBlogController:removeArticle');
  $app->put('/api/blogArticle/updatePriority', '\AdminBlogController:updatePriority');

  //Restore
  $app->get('/restore', '\AdminRestoreController:fetch');
  $app->get('/api/restore/{type}', '\AdminRestoreController:get');

  //user
  $app->get('/user', '\AdminUserController:fetch');
  $app->get('/user/create', '\AdminUserController:create');
  $app->get('/user/{id}', '\AdminUserController:get');
  $app->post('/user', '\AdminUserController:store');
  $app->put('/user/{id}', '\AdminUserController:update');
  $app->delete('/user/{id}', '\AdminUserController:delete');

  //role
  $app->get('/role', '\AdminRoleController:fetch');
  $app->get('/role/create', '\AdminRoleController:create');
  $app->get('/role/{id}', '\AdminRoleController:get');
  $app->post('/role', '\AdminRoleController:store');
  $app->put('/role/{id}', '\AdminRoleController:update');
  $app->delete('/role/{id}', '\AdminRoleController:delete');

  //article
  $app->get('/article', '\AdminArticleController:fetch');
  $app->get('/article/create', '\AdminArticleController:create');
  $app->get('/article/comment', '\AdminArticleController:comment');
  $app->get('/article/{id}', '\AdminArticleController:get');
  $app->post('/article', '\AdminArticleController:store');
  $app->put('/article/{id}', '\AdminArticleController:update');
  $app->delete('/article/{id}', '\AdminArticleController:delete');
  $app->post('/article/duplicate/{id}', '\AdminArticleController:duplicate');
  $app->get('/api/getArticlePaginate', '\AdminArticleController:getArticlePaginate');
  $app->post('/api/exportArticleExcel', '\AdminArticleController:exportArticleExcel');



  //blog
  $app->get('/blog', '\AdminBlogController:fetch');
  $app->get('/blog/create', '\AdminBlogController:create');
  $app->get('/blog/{id}', '\AdminBlogController:get');
  $app->post('/blog', '\AdminBlogController:store');
  $app->put('/blog/{id}', '\AdminBlogController:update');
  $app->delete('/blog/{id}', '\AdminBlogController:delete');
  //game
  $app->get('/game', '\AdminGameController:fetch');
  $app->get('/game/create', '\AdminGameController:create');
  $app->get('/game/{id}', '\AdminGameController:get');
  $app->post('/game', '\AdminGameController:store');
  $app->put('/game/{id}', '\AdminGameController:update');
  $app->delete('/game/{id}', '\AdminGameController:delete');

  //page
  $app->get('/page', '\AdminPageController:fetch');
  $app->get('/page/create', '\AdminPageController:create');
  $app->get('/page/{id}', '\AdminPageController:get');
  $app->post('/page', '\AdminPageController:store');
  $app->put('/page/{id}', '\AdminPageController:update');
  $app->delete('/page/{id}', '\AdminPageController:delete');

  //bank
  $app->get('/bank', '\AdminBankController:fetch');
  $app->get('/bank/create', '\AdminBankController:create');
  $app->get('/bank/{id}', '\AdminBankController:get');
  $app->post('/bank', '\AdminBankController:store');
  $app->put('/bank/{id}', '\AdminBankController:update');
  $app->delete('/bank/{id}', '\AdminBankController:delete');

  //product *
  $app->get('/product', '\AdminProductController:fetch');
  $app->get('/product/create', '\AdminProductController:create');
  $app->get('/product/updateStock', '\AdminProductController:updateStock');
  $app->get('/product/comment', '\AdminProductController:comment');
  $app->get('/product/{id}', '\AdminProductController:get');
  $app->post('/product', '\AdminProductController:store');
  $app->put('/product/{id}', '\AdminProductController:update');
  $app->delete('/product/{id}', '\AdminProductController:delete');
  $app->post('/product/duplicate/{id}', '\AdminProductController:double');
  $app->get('/api/getProductPaginate', '\AdminProductController:getProductPaginate');

  //dashboard
  $app->get('/api/dashboard/getrevenue', '\AdminDashboardController:getRevenue');
  $app->get('/api/dashboard/getvisit', '\AdminDashboardController:getVisit');

  //order
  $app->get('/api/orders', '\AdminOrderController:list');
  $app->post('/api/order', '\AdminOrderController:store');
  $app->get('/api/orders/draft', '\AdminOrderController:draft');
  $app->get('/api/order/{id}', '\AdminOrderController:detail');
  $app->put('/api/order/{id}', '\AdminOrderController:updateAPI');
  $app->get('/api/region', '\AdminSettingController:region');
  $app->get('/api/region/{id}', '\AdminSettingController:subregion');
  $app->get('/api/getOrderPaginate', '\AdminOrderController:getOrderPaginate');
  $app->post('/api/exportOrderExcel', '\AdminOrderController:exportOrderExcel');

  //product
  $app->get('/api/products', '\AdminProductController:list');
  $app->get('/api/product/{id}', '\AdminProductController:detail');
  $app->post('/api/product', '\AdminProductController:storeAPI');
  $app->put('/api/product/{id}', '\AdminProductController:updateAPI');
  $app->delete('/api/product/{id}', '\AdminProductController:delete');

  // collection
  $app->get('/api/collections', '\AdminCollectionController:list');
  $app->get('/api/collection/{id}', '\AdminCollectionController:detail');
  $app->post('/api/collection', '\AdminCollectionController:store');
  $app->put('/api/collection/{id}', '\AdminCollectionController:update');
  $app->delete('/api/collection/{id}', '\AdminCollectionController:delete');

  //Customer
  $app->get('/api/customer', '\AdminCustomerController:list');
  $app->post('/api/customer', '\AdminCustomerController:store');
  $app->get('/api/customer/{id}', '\AdminCustomerController:detail');
  $app->put('/api/customer/{id}', '\AdminCustomerController:update');
  $app->get('/api/subscriber', '\AdminCustomerController:apiSubscriber');
  $app->get('/api/contact', '\AdminContactController:list');
  $app->get('/api/contact/{id}', '\AdminContactController:detail');
  $app->get('/api/review', '\AdminReviewController:list');
  $app->get('/api/comment', '\AdminCommentController:list');

  // $app->get('/api/comment', '\AdminCommentController:list');

  // article
  $app->get('/api/articles', '\AdminArticleController:list');
  $app->get('/api/article/{id}', '\AdminArticleController:detail');
  $app->post('/api/article', '\AdminArticleController:store');
  $app->put('/api/article/{id}', '\AdminArticleController:update');
  $app->delete('/api/article/{id}', '\AdminArticleController:delete');

  // blog
  $app->get('/api/blogs', '\AdminBlogController:list');
  $app->get('/api/blog/{id}', '\AdminBlogController:detail');
  $app->post('/api/blog', '\AdminBlogController:store');
  $app->put('/api/blog/{id}', '\AdminBlogController:update');
  $app->delete('/api/blog/{id}', '\AdminBlogController:delete');

  // page
  $app->get('/api/pages', '\AdminPageController:list');
  $app->get('/api/page/{id}', '\AdminPageController:detail');
  $app->post('/api/page', '\AdminPageController:store');
  $app->put('/api/page/{id}', '\AdminPageController:update');
  $app->delete('/api/page/{id}', '\AdminPageController:delete');

  //coupon
  $app->get('/api/coupons', '\AdminCouponController:list');
  $app->post('/api/coupon', '\AdminCouponController:store');
  $app->get('/api/coupon/{id}', '\AdminCouponController:detail');
  $app->put('/api/coupon/{id}', '\AdminCouponController:update');
  $app->delete('/api/coupon/{id}', '\AdminCouponController:delete');

  //sale
  $app->get('/api/salesAPI', '\AdminSaleController:list');
  $app->post('/api/saleAPI', '\AdminSaleController:store');
  $app->get('/api/saleAPI/{id}', '\AdminSaleController:detail');
  $app->put('/api/saleAPI/{id}', '\AdminSaleController:update');
  $app->delete('/api/saleAPI/{id}', '\AdminSaleController:delete');
  $app->get('/api/sale/getproduct', '\AdminSaleController:getProduct');
  $app->post('/api/sale/getproductFromTag', '\AdminSaleController:getproductFromTag');

  // gallery
  $app->get('/api/galleries', '\AdminGalleryController:list');
  $app->get('/api/gallery/{id}', '\AdminGalleryController:detail');
  $app->post('/api/gallery', '\AdminGalleryController:store');
  $app->put('/api/gallery/{id}', '\AdminGalleryController:update');
  $app->delete('/api/gallery/{id}', '\AdminGalleryController:delete');
  $app->get('/api/photo/{id}', '\AdminPhotoController:detail');

  // menu
  $app->get('/api/menus', '\AdminMenuController:list');

  // setting
  $app->get('/api/view/setting', '\AdminSettingController:settingView');
  $app->get('/api/view/settingDataAPI', '\AdminSettingController:settingDataAPI');
  $app->get('/api/loadSettingAPI', '\AdminSettingController:loadSettingAPI');
  $app->get('/api/getSetting', '\AdminSettingController:getSetting');
  $app->get('/api/object/{type}', '\AdminSettingController:getObject');

  // user
  $app->get('/api/users', '\AdminUserController:list');
  $app->get('/api/user/{id}', '\AdminUserController:detail');
  $app->post('/api/user', '\AdminUserController:store');
  $app->put('/api/user/{id}', '\AdminUserController:update');

  // history
  $app->get('/api/history', '\AdminUserController:apiHistory');

  // shipping
  $app->get('/api/shippings', '\AdminShippingFeeController:list');
  $app->get('/api/shipping/{region_id}', '\AdminShippingFeeController:detail');
  $app->post('/api/shipping', '\AdminShippingFeeController:store');
  $app->put('/api/shipping/{id}', '\AdminShippingFeeController:update');

  //api dashboard
  $app->post('/api/dashboard/getrevenue', '\AdminDashboardController:getRevenue');
  $app->post('/api/dashboard/getvisit', '\AdminDashboardController:getVisit');

  // role
  $app->get('/api/roles', '\AdminRoleController:list');
  $app->get('/api/role/{id}', '\AdminRoleController:detail');
  $app->post('/api/role', '\AdminRoleController:store');
  $app->put('/api/role/{id}', '\AdminRoleController:update');

  // deleted
  $app->get('/api/deleted', '\AdminDeletedController:list');
  $app->get('/api/deleted/{type}', '\AdminDeletedController:detail');
  $app->post('/api/deleted', 'restoreStatus');

  //attributes
  $app->get('/api/attributes/fetch', '\AdminAttributeController:fetch');

  //css
  $app->get('/api/css', '\AdminSettingController:fetchCSS');
  $app->post('/api/css', '\AdminSettingController:updateCSS');
  $app->get('/api/loadCSS', '\AdminSettingController:loadCSS');
  $app->get('/api/css/version', '\AdminSettingController:getVersionCSS');

  //testimonial
  $app->get('/api/testimonial', '\AdminTestimonialController:list');
  $app->get('/api/testimonial/{id}', '\AdminTestimonialController:detail');
  $app->post('/api/testimonial', '\AdminTestimonialController:store');
  $app->put('/api/testimonial/{id}', '\AdminTestimonialController:update');

  //client
  $app->get('/api/client', '\AdminClientController:list');
  $app->get('/api/client/{id}', '\AdminClientController:detail');
  $app->post('/api/client', '\AdminClientController:store');
  $app->put('/api/client/{id}', '\AdminClientController:update');

  //mail
  $app->get('/api/email-templates/{name}', '\AdminEmailTemplateController:detail');
  $app->put('/api/email-templates', '\AdminEmailTemplateController:update');


  //customfield
  $app->get('/api/customfield', 'getCustomFieldType');
  $app->get('/api/getThemeViewAPI', 'getViewTemplateAPI');

  //upload zip file

  if (function_exists('add_custom_admin_routes')) {
    add_custom_admin_routes($app);
  }

  // Elfinder
  $app->get('/connector', '\AdminElfinderController:finder');
  $app->post('/connector', '\AdminElfinderController:finder');
  $app->put('/connector', '\AdminElfinderController:finder');
  $app->delete('/connector', '\AdminElfinderController:finder');

  //Template Mail
  $app->get('/email-templates/{name}', '\AdminEmailTemplateController:get');
  $app->put('/email-templates', '\AdminEmailTemplateController:update');

})->add(function ($request, $response, $next) {
  $attrs = $request->getAttributes();
  if ($attrs['login']) {
    $user = User::find($attrs['user_id']);
    if (!$user) {
      unset($_SESSION['login']);
      unset($_SESSION['user_id']);
      unset($_SESSION['email']);
      unset($_SESSION['name']);
      unset($_SESSION['role']);
      return $response->withStatus(302)->withHeader('Location', '/admin/login');
    }
  } else {
    $_SESSION['href'] = $request->getUri()->getPath();
    return $response->withStatus(302)->withHeader('Location', '/admin/login');
  }

  //TODO: Phan quyen

  $method = $request->getMethod();
  $uri = $request->getUri();
  $uri = strtok($uri, '?');

  if (!checkPermission($user, $method, $uri)) {
    return $response->withHeader('Location', '/admin/403');
  }

  $GLOBALS['attrs'] = $attrs;

  return $next($request, $response);

});
