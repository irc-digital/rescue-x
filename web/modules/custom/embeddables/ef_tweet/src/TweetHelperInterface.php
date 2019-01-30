<?php

namespace Drupal\ef_tweet;

use Drupal\ef\EmbeddableInterface;

interface TweetHelperInterface {
  /**
   * Called when a tweet embeddable is being saved
   *
   * @param \Drupal\ef_tweet\EmbeddableInterface $tweet
   * @return mixed
   */
  public function presaveTweet (EmbeddableInterface $tweet);
}