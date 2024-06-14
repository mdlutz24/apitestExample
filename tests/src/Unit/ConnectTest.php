<?php

namespace Drupal\Tests\my_module\Unit;

use Drupal\api_tests\ConnectMiddleware;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;

/**
 * Tests the Connect class.
 *
 *
 * @group connect
 */
class ConnectTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Ensure we have a new container.
    \Drupal::unsetContainer();


    // Create a guzzle client that contains our custom middleware.
    $stack = new HandlerStack();
    $stack->setHandler(new CurlHandler());
    $middleware = new ConnectMiddleware();
    $stack->push($middleware->returnPromise(), 'AFTConnectMiddleware');
    $http_client = new HttpClient(['handler' => $stack]);

    // Instantiate the connector.

  }

}
