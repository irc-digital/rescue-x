<?php

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function ef_social_share_token_info() {
  $node['custom-social-share-title'] = [
    'name' => t("Social share page title"),
    'description' => t("A token that can be used for the social share title. This will just be the page title unless otherwise intervened upon."),
  ];

  return [
    'tokens' => ['node' => $node],
  ];
}

/**
 * Implements hook_tokens().
 */
function ef_social_share_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $token_service = \Drupal::token();

  $replacements = [];

  if ($type == 'node' & !empty($data['node'])) {
    $node = $data['node'];

    foreach ($tokens as $name => $original) {
      if ($name == 'custom-social-share-title') {
        $replacements[$original] = 'Hello world';
      }
    }
  }

  return $replacements;
}
