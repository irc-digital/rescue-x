<?php


namespace Drupal\ef_tweet;


use Drupal\Component\Utility\UrlHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class TwitterService implements TwitterServiceInterface {
  /** @var \GuzzleHttp\Client */
  protected $httpClient;

  public function __construct(Client $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * @inheritdoc
   */
  public function getTweetEmbedCode($tweet_url) {
    $encoded_tweet_url = UrlHelper::encodePath($tweet_url);

    $embed_code = '';

    try {
      $response = $this->httpClient->get('https://publish.twitter.com/oembed?omit_script=true&url=' . $encoded_tweet_url);

      if ($response->getStatusCode() == 200) {
        $result = json_decode($response->getBody());

        if (isset($result->html)) {
          $embed_code = $result->html;
        }
      }
    } catch (RequestException $e) {
      watchdog_exception('twitter_service', $e->getMessage());
    }

    return $embed_code;

  }

}