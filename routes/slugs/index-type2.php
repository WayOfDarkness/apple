<?php

  use Slim\Container as ContainerInterface;

  function getResponseFromPostType($langCode, $customPostType) {

    global $adminSettings;
    $langCode = $langCode ? $langCode : 'vi';

    $slugMeta = array();

    $lang = array("vi", "en");

    $postTypeList = array("page", "collection", "article", "blog", "product", "gallery");

    foreach ($lang as $key => $l) {
      foreach ($postTypeList as $key => $pt) {
        $custom = $adminSettings["slug_type_2_" . $pt . "_" . $l];
        $slugMeta[$custom . "_" . $l] = $pt;
      }
    }

    $postType = $slugMeta[$customPostType . "_" . $langCode];

    switch ($postType) {
      case 'gallery':
        $ctrl = new GalleryController(new ContainerInterface);
        break;
      case 'collection':
        $ctrl = new CollectionController(new ContainerInterface);
        break;
      case 'article';
        $ctrl = new ArticleController(new ContainerInterface);
        break;
      case 'product';
        $ctrl = new ProductController(new ContainerInterface);
        break;
      case 'blog';
        $ctrl = new BlogController(new ContainerInterface);
        break;
      default:
        $ctrl = new PageController(new ContainerInterface);
        break;
    }

    return [
      "ctrl" => $ctrl,
      "postType" => $postType
    ];
  }

  // custom slug
  // TODO: need to test
  $app->get('/{customPostType}', function($request, $response) {
    $customPostType = $request->getAttribute('customPostType');
    $langCode = $request->getAttribute("lang");
    $responseData = getResponseFromPostType($langCode, $customPostType);
    if ($responseData["postType"] == "collection" || $responseData["postType"] == "blog") {
      $response = $responseData["ctrl"]->fetch($request, $response);
      return $response;
    }

    if (empty($responseData["postType"])) {
      return $responseData["ctrl"]->PageNotFound($request, $response);
    }
    return $response->withStatus(340)->withHeader('Location', '/404');
  });

  $app->get('/{customPostType}/{handle}', function($request, $response) {
    $customPostType = $request->getAttribute('customPostType');
    $langCode = $request->getAttribute("lang");
    $handle = $request->getAttribute('handle');
    $responseData = getResponseFromPostType($langCode, $customPostType);
    if (empty($responseData["postType"])) return $responseData["ctrl"]->PageNotFound($request, $response);
    $response = $responseData["ctrl"]->get($request, $response);
    return $response;
  });
