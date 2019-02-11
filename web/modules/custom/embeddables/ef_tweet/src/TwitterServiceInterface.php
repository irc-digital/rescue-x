<?php


namespace Drupal\ef_tweet;


interface TwitterServiceInterface {
  /**
   * Uses the Twitter API to retrieve the embed code for the supplied URL
   *
   * @param $tweet_url
   * @return string - the HTML of the embed code
   */
  public function getTweetEmbedCode ($tweet_url);
}