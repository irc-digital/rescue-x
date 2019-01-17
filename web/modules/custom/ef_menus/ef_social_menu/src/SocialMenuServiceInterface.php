<?php

namespace Drupal\ef_social_menu;

interface SocialMenuServiceInterface {
  /**
   * Returns an ordered associative array where the key of the array is the
   * icon used for the social site and the value is the URL of the site.
   *
   * This will use the current active language to ensure a proper language-specific
   * output is returned
   *
   * @return mixed
   */
  function getSocialSites ();
}