<?php

namespace Drupal\api_tests;

use Drupal\Core\Serialization\Yaml;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Query;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

/**
 * Guzzle middleware for the API.
 */
class ConnectMiddleware {

  const HOST = 'testapi.example.com';
  /**
   * Invoked method that returns a promise.
   */
  public function returnPromise() {
    return function (callable $handler) {
      return function (RequestInterface $request, array $options) use ($handler) {
        $uri = $request->getUri();

        // If the host matches our test host, return our mock promise.
        if ($uri->getHost() === self::HOST) {
          return $this->createPromise($request);
        }

        // Otherwise, no intervention. We defer to the handler stack.
        return $handler($request, $options);
      };
    };
  }

  /**
   * Creates a promise for the AFT Connect request.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request interface.
   *
   * @return \GuzzleHttp\Promise\PromiseInterface
   *   The promise interface.
   */
  protected function createPromise(RequestInterface $request) {
    $request_data = [];
    $uri = $request->getUri();
    $request_data['uri_path'] = $uri->getPath();
    $request_data['params'] = Query::parse($uri->getQuery());
    $mock_data_path = __DIR__ . "/../mock_data";

    $json = FALSE;
    $status = 200;

    if (file_exists("$mock_data_path/responses.yml")) {
      $mock_data_array = Yaml::decode(file_get_contents("$mock_data_path/responses.yml"));

      foreach ($mock_data_array as $mock_data) {
        // Check that the path matches.
        if ($mock_data['uri_path'] == $request_data['uri_path']) {
          $params_match = TRUE;
          // Check that all params match.
          foreach ($request_data['params'] as $key => $value) {
            if (array_search($value, $mock_data['params']) != $key) {
              $params_match = FALSE;
            }
          }
          // If everything matches, return the response.
          if ($params_match) {
            $json = $mock_data['response'];
            $status = $mock_data['response_status'] ?? 200;
          }
        }
      };
    }

    if ($json === FALSE) {
      $json = file_get_contents("$mock_data_path/404.json");
    }

    $response = new Response($status, [], $json);
    return new FulfilledPromise($response);

  }

}
