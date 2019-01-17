<?php

namespace Drupal\ef_crisis_watch;

interface CrisisWatchServiceInterface {
  /**
   * Returns an array with the crisis watch information for the current language
   *
   * Will return an array with keys title and url else NULL if no crisis
   * watch is set for the active language
   *
   * @return mixed
   */
  function getCrisisWatch ();
}