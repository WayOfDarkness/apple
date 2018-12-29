<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once("../models/Page.php");

class PageController extends Controller {

  public function get(Request $request, Response $response) {

    $handle = $request->getAttribute('handle');
    $page = Slug::getObjFromHandle($handle, "page");

    if (!$page) {
      $this->view->render($response, '404');
      return $response->withStatus(404);
    }

    $page->view = $page->view + 1;
    $page->save();

    if ($page->tags) {
      $page->tags = substr($page->tags, 1);
      $page->tags = substr($page->tags, 0, -1);
      $page['tags'] = explode("#", $page->tags);
    }

    Slug::addHandleToObj($page, "page");

    if ($_SESSION['lang'] != 'vi') {
      translatePost($page, 'page');
    }

    // Array metafield
    $page->metafields = [];
    $metafields = Metafield::where('post_id', $page->id)->where('post_type', 'page')->get();
    if ($metafields && count($metafields)){
      if ($_SESSION['lang'] != 'vi') translateMetafield($metafields);
      $page->metafields = $metafields;
    }

    $view_template = 'page';
    if ($page->template) $view_template = 'page.' . $page->template;
    if ($_GET['view']) $view_template = 'page.' . $_GET['view'];

    return $this->view->render($response, $view_template, [
      'id' => $page->id,
      'page' => $page
    ]);
  }

  public function contact(Request $request, Response $response) {

    $handle = $_SESSION['lang'] == 'en' ? 'contact' : 'lien-he';
    $page = Slug::getObjFromHandle($handle, "page");

    if (!$page) {
      $this->view->render($response, '404');
      return $response->withStatus(404);
    }

    $page->view = $page->view + 1;
    $page->save();

    if ($page->tags) {
      $page->tags = substr($page->tags, 1);
      $page->tags = substr($page->tags, 0, -1);
      $page['tags'] = explode("#", $page->tags);
    }

    Slug::addHandleToObj($page, "page");

    if ($_SESSION['lang'] != 'vi') {
      $translation = PageTranslations::where([
        ['page_id', $page->id],
        ['lang', $_SESSION['lang']]
      ])->first();

      $page->title = $translation->title ?: $page->title;
      $page->description = $translation->description ?: $page->description;
      $page->content = $translation->content ?: $page->content;
    }

    // Array metafield
    $page->metafields = [];
    $metafields = Metafield::where('post_id', $page->id)->where('post_type', 'page')->get();
    if ($metafields && count($metafields)){
      if ($_SESSION['lang'] != 'vi') translateMetafield($metafields);
      $page->metafields = $metafields;
    }

    return $this->view->render($response, 'page.contact', [
      'id' => $page->id,
      'page' => $page
    ]);
  }

  public function introduce(Request $request, Response $response) {

    $handle = $_SESSION['lang'] == 'en' ? 'about-us' : 'gioi-thieu';
    $page = Slug::getObjFromHandle($handle, "page");

    if (!$page) {
      $this->view->render($response, '404');
      return $response->withStatus(404);
    }

    $page->view = $page->view + 1;
    $page->save();

    if ($page->tags) {
      $page->tags = substr($page->tags, 1);
      $page->tags = substr($page->tags, 0, -1);
      $page['tags'] = explode("#", $page->tags);
    }

    Slug::addHandleToObj($page, "page");

    if ($_SESSION['lang'] != 'vi') {
      $translation = PageTranslations::where([
        ['page_id', $page->id],
        ['lang', $_SESSION['lang']]
      ])->first();

      $page->title = $translation->title ?: $page->title;
      $page->description = $translation->description ?: $page->description;
      $page->content = $translation->content ?: $page->content;
    }

    // Array metafield
    $page->metafields = [];
    $metafields = Metafield::where('post_id', $page->id)->where('post_type', 'page')->get();
    if ($metafields && count($metafields)){
      if ($_SESSION['lang'] != 'vi') translateMetafield($metafields);
      $page->metafields = $metafields;
    }

    return $this->view->render($response, 'page.about_us', [
      'id' => $page->id,
      'page' => $page
    ]);
  }

  public function PageNotFound(Request $request, Response $response) {
    $this->view->render($response, '404');
    return $response->withStatus(404);
  }
}
