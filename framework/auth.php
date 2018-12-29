<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;

class JwtAndSessionAuth
{
    /**
     * Example middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function getMixedSessionAndJwtParams($request) {
      if (session_status() == PHP_SESSION_NONE) session_start();
      try {
        $cookie = $request->getCookieParams();
        if (isset($cookie['jwt'])) {
            $attrs = JwtAndSessionAuth::decodeJwt($cookie['jwt']);
            return array_merge($_SESSION, $attrs);
        }
      } catch (Exception $e) {
        return $_SESSION;
      }

      return $_SESSION;
    }

    public static function decodeJwt($jwt) {
      $JWT_SECRET = getenv('JWT_SECRET') ? getenv('JWT_SECRET') : 'sjjZlW4VXCtqgcOYxAXtaxu2QJLCzwQRw';
      $attrs = (array) JWT::decode($jwt, $JWT_SECRET, array('HS256'));
      return $attrs;
    }

    public static function encodeJwt($arr) {
      $JWT_SECRET = getenv('JWT_SECRET') ? getenv('JWT_SECRET') : 'sjjZlW4VXCtqgcOYxAXtaxu2QJLCzwQRw';
      $jwt = JWT::encode($arr, $JWT_SECRET);
      return $jwt;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        $SS = $this->getMixedSessionAndJwtParams($request);
        $request = $request->withAttributes($SS);
        return $next($request, $response);
    }
}
