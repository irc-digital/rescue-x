<?php

namespace Drupal\ef_tweet;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\ef\EmbeddableInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TweetHelper implements TweetHelperInterface, ContainerInjectionInterface {

  /**
   * @var \Drupal\ef_tweet\TwitterServiceInterface
   */
  protected $twitterService;

  public function __construct(TwitterServiceInterface $twitterService) {
    $this->twitterService = $twitterService;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ef.twitter_service')
    );
  }

  /**
   * @inheritdoc
   */
  public function presaveTweet(EmbeddableInterface $tweet) {
    $tweet_url = $tweet->field_tweet_url->value;

    $tweet_embed_code = $this->twitterService->getTweetEmbedCode($tweet_url);

    if (strlen($tweet_embed_code) > 0) {
      $tweet->field_tweet_tweet = $tweet_embed_code;
    }
  }

}