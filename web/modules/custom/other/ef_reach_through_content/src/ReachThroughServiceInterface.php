<?php

namespace Drupal\ef_reach_through_content;

interface ReachThroughServiceInterface {
  /**
   * Return an array of the fields that can be mapped on the supplied reach-through entity bundle
   *
   * @return mixed
   */
  public function geReachThroughFields ($reach_through_bundle);
}