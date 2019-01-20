<?php

require_once("../models/helper.php");
require_once('../controllers/IndexController.php');
require_once('../controllers/CollectionController.php');
require_once('../controllers/ProductController.php');
require_once('../controllers/PageController.php');
require_once("../controllers/GalleryController.php");
require_once('../controllers/OrderController.php');
require_once('../controllers/ArticleController.php');
require_once('../controllers/BlogController.php');
require_once('../controllers/UserController.php');
require_once('../controllers/TestController.php');
require_once('../controllers/ImageController.php');
require_once('../controllers/CustomerController.php');
require_once('../controllers/CommentController.php');
require_once('../controllers/CouponController.php');
require_once('../controllers/ContactController.php');
require_once('../controllers/SlugController.php');
require_once('../controllers/ReactController.php');
require_once("../controllers/PageController.php");
require_once("../controllers/ShippingFeeController.php");
require_once("../controllers/WishlistController.php");
require_once("../controllers/ReviewController.php");
require_once('../models/helper.php');
require_once('../models/Statistic.php');
require_once('../framework/config.php');

if (function_exists('add_custom_store_front_routes')) {
  add_custom_store_front_routes($app);
}

function setupLanguageRoutes($app) {
  //collection
  $app->get('/tat-ca-san-pham', '\CollectionController:all');
  $app->get('/san-pham', '\CollectionController:all');
  $app->get('/nhom-san-pham/tat-ca', '\CollectionController:all');
  $app->get('/collection/all', '\CollectionController:all');

  $app->get('/san-pham/tat-ca', '\CollectionController:all');
  $app->get('/product/all', '\CollectionController:all');

  //blog

  $app->get('/bai-viet/tat-ca', '\BlogController:all');
  $app->get('/article/all', '\BlogController:all');

  $app->get('/tat-ca-bai-viet', '\BlogController:all');
  $app->get('/nhom-bai-viet/tat-ca', '\BlogController:all');
  $app->get('/blog/all', '\BlogController:all');

  //search
  $app->get('/tim-kiem', '\CollectionController:search');
  $app->get('/search', '\CollectionController:search');

  //customer
  $app->get('/dang-nhap', '\CustomerController:login');
  $app->get('/login', '\CustomerController:login');

  $app->get('/dang-ky', '\CustomerController:register');
  $app->get('/register', '\CustomerController:register');

  $app->get('/khach-hang', '\CustomerController:customer');
  $app->get('/customer', '\CustomerController:customer');

  $app->get('/khach-hang/tong-quan', '\CustomerController:dashboard');
  $app->get('/customer/dashboard', '\CustomerController:dashboard');

  $app->get('/khach-hang/don-hang', '\CustomerController:customerOrder');
  $app->get('/customer/order', '\CustomerController:customerOrder');

  $app->get('/khach-hang/danh-sach-don-hang', '\CustomerController:orders');
  $app->get('/customer/orders', '\CustomerController:orders');
  $app->get('/customer/order/{id}', '\CustomerController:orderDetail');

  $app->get('/khach-hang/doi-mat-khau', '\CustomerController:changePassword');
  $app->get('/customer/change-password', '\CustomerController:changePassword');

  $app->get('/khach-hang/quen-mat-khau', '\CustomerController:forgotPassword');
  $app->get('/customer/forgot-password', '\CustomerController:forgotPassword');

  $app->get('/khach-hang/diem-tich-luy', '\CustomerController:savePoint');
  $app->get('/customer/save-point', '\CustomerController:savePoint');

  $app->get('/khach-hang/ma-gioi-thieu', '\CustomerController:referral');
  $app->get('/customer/referral', '\CustomerController:referral');

  $app->get('/khach-hang/tao-mat-khau/{token}', '\CustomerController:createPassword');
  $app->get('/customer/create-password/{token}', '\CustomerController:createPassword');

  $app->get('/khach-hang/chinh-sua-tai-khoan', '\CustomerController:editAccount');
  $app->get('/customer/edit-account', '\CustomerController:editAccount');

  $app->get('/khach-hang/san-pham-yeu-thich', '\CustomerController:wishlist');
  $app->get('/customer/wishlist', '\CustomerController:wishlist');

  // $app->get('/customer/all-review', '\CustomerController:allReview');

  // $app->get('/customer/review', '\CustomerController:customerReview');
  // $app->get('/customer/reviewed', '\CustomerController:reviewed');
  // $app->get('/product/review', '\CustomerController:productReview');
  // $app->get('/customer/review/{id}', '\CustomerController:reviewDetail');

  $app->get('/san-pham-yeu-thich', '\CustomerController:wishlist');
  $app->get('/wishlist', '\CustomerController:wishlist');

  //order
  $app->get('/gio-hang', '\OrderController:cart');
  $app->get('/cart', '\OrderController:cart');

  $app->get('/dat-hang', '\OrderController:checkOut');
  $app->get('/checkout', '\OrderController:checkOut');

  $app->get('/dat-hang/dang-nhap', '\OrderController:checkoutLogin');
  $app->get('/checkout/login', '\OrderController:checkoutLogin');

  $app->get('/dat-hang/dang-ky', '\OrderController:checkoutRegister');
  $app->get('/checkout/register', '\OrderController:checkoutRegister');

  $app->get('/dat-hang/thanh-cong', '\OrderController:success');
  $app->get('/checkout/success', '\OrderController:success');

  $app->get('/dat-hang-thanh-cong', '\OrderController:orderSuccess');
  $app->get('/order-success', '\OrderController:orderSuccess');

  //contact
  $app->get('/lien-he', '\ContactController:contact');
  $app->get('/contact', '\ContactController:contact');
}

function setupApiRoutes($app) {
  //search API
  $app->get('/api/search', '\IndexController:searchAPI');
  $app->get('/api/article/fulltext-search', '\ArticleController:fulltextSearch');
  $app->get('/api/article/fuzzy-search', '\ArticleController:fuzzySearch');

  $app->get('/api/product/fulltext-search', '\ProductController:fulltextSearch');
  $app->get('/api/product/fuzzy-search', '\ProductController:fuzzySearch');

  //comment
  $app->post('/api/comment', '\CommentController:store');

  //coupon
  $app->post('/api/checkCoupon', '\CouponController:checkCoupon');

  //contact
  $app->post('/api/contact', '\ContactController:store');
  $app->get('/api/contact/{id}/sendEmailAdmin', '\ContactController:sendEmailAdmin');



  //customer
  $app->post('/api/signin', '\CustomerController:signin');
  $app->post('/api/signup', '\CustomerController:signup');
  $app->get('/api/signout', '\CustomerController:signout');
  $app->post('/api/changeInformation', '\CustomerController:apiChangeInformation');
  $app->post('/api/changePassword', '\CustomerController:apiChangePassword');
  $app->post('/api/forgotPassword', '\CustomerController:apiForgotPassword');
  $app->post('/api/resetPassword', '\CustomerController:apiResetPassword');
  $app->post('/api/subscriber', '\CustomerController:subscriber');
  $app->get('/api/subscriber/{id}/sendEmailAdmin', '\CustomerController:sendEmailAdmin');
  //customer address book
  $app->post('/api/create/AddressBook', '\CustomerController:createAddressBook');
  $app->post('/api/update/AddressBook/{id}', '\CustomerController:updateAddressBook');
  $app->get('/api/get/AddressBook', '\CustomerController:getAddressBook');
  $app->delete('/api/delete/AddressBook/{id}', '\CustomerController:deleteAddressBook');
  $app->post('/api/update/AddressBookDefault/{id}', '\CustomerController:updateDefaultAddress');

  //session
  $app->post('/api/setSession', 'IndexController:setSession');
  $app->get('/api/destroySession', 'IndexController:destroySession');

  //wishlist
  $app->post('/api/wishlist', '\WishlistController:store');
  $app->delete('/api/wishlist', '\WishlistController:delete');

  //cart
  $app->post('/api/cart/add', '\OrderController:addToCart');
  $app->post('/api/cart/change', '\OrderController:changeCart');
  $app->post('/api/cart/clear', '\OrderController:clearCart');
  $app->get('/api/cart/getCart', '\OrderController:getCart');
  $app->get('/api/getSale', '\OrderController:getSale');

  //order
  $app->post('/api/order', '\OrderController:store');
  $app->get('/api/region/{id}/listSubRegion', '\OrderController:listSubRegion');
  $app->get('/api/order/{id}/sendEmail', '\OrderController:sendEmail');
  $app->get('/api/order/{id}/sendEmailAdmin', '\OrderController:sendEmailAdmin');

  //shipping
  $app->get('/api/getShipping', '\ShippingFeeController:getShipping');

  //sitemap
  $app->get('/sitemap', 'sitemap_index');
  $app->get('/sitemap.xml', 'sitemap_index');
  $app->get('/sitemap/{type}.xml', 'sitemap_group'); //article, page, blog, product, collection
  $app->get('/sitemap/{type}', 'sitemap_group'); //article, page, blog, product, collection

  //product
  $app->get('/api/getInformationVariant', 'ProductController:getInformationVariant');
  $app->post('/api/fastBuy', 'ProductController:fastBuy');

  //backup DB
  $app->get('/api/exportDB', '\AdminExportController:exportDB');

  //upload file
  $app->post('/api/uploadFile', 'uploadFile');

  //pixel
  $app->get('/pixel/visit', function ($request, $response, $args) {
    $data = [
      'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ];
    Statistic::createOrUpdate($data);
    $img = '/static/img/1px.png';
    return $response->withStatus(200)->withHeader('Content-type', 'image/png')->write($img);
  });

  //review
  $app->get('/api/review', '\ReviewController:fetch');
  $app->get('/api/customer/review', '\ReviewController:customerReviewAction');
  $app->get('/api/customer/listReview', '\ReviewController:listReview');
  $app->post('/api/review', '\ReviewController:store');
  $app->post('/api/review/{id}/like', '\ReviewController:like');
  $app->post('/api/review/{id}/dislike', '\ReviewController:dislike');



  // React

  $app->post('/api/react/{id}/like', '\ReactController:like');
  $app->post('/api/react/{id}/dislike', '\ReactController:dislike');

  //metafield
  $app->post('/api/metafield', 'updateMetafield');

  //404
  $app->get('/404', '\PageController:PageNotFound');

  //image
  $app->get('/api/image', '\ImageController:get');

  //other
  $app->get('/api/san-pham/search', 'smartSearch');
  $app->get('/user/forgotpassword', '\UserController:forgotPassword');
  $app->post('/api/user/login', '\UserController:checkLogin');
  $app->put('/api/user/password', '\UserController:updatePassword');
  $app->get('/api/user/checkEmail', '\UserController:resetPassword');
  $app->get('/api/variant/{id}', '\ProductController:getProductOfVariant');
  $app->get('/api/product/{id}', '\ProductController:getProduct');

  //social login callback
  $app->get('/facebook-login', '\CustomerController:fbLogin');
  $app->get('/facebook/callback', '\CustomerController:facebookCallback');
  $app->get('/google/callback', '\CustomerController:googleCallback');

  $app->post('/api/freshdesk-ticket', '\IndexController:freshdeskTicket');

  // const for client
  $app->get('/api/const', '\IndexController:fetchConst');
  $app->get('/js/const', '\IndexController:jsConst');

}
