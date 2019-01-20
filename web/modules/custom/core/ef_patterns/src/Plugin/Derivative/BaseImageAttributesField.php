<?php

namespace Drupal\ef_patterns\Plugin\Derivative;

/**
 * Base class that provides the image attributes we want
 */
abstract class BaseImageAttributesField extends UnpackedAttributeField {

  protected function getFieldAttributes() {
    return [
      'srcset' => 'Source set',
      'sources' => 'Sources - for art-direction',
      'sizes' => 'Sizes',
      'fallback_uri' => 'Fallback URL',
      'alt' => 'Alt attribute',
    ];
  }
}