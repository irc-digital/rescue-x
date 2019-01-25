<?php

namespace Drupal\ef_curated_content_wrapper;

interface CuratedContentServiceInterface {
  /**
   * Return an array of the fields that can be mapped on the curated content entries bundle
   *
   * @return mixed
   */
  public function getCuratedContentFields ();
}