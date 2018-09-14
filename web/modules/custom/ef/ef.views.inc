<?php

/**
 * Implements hook_views_data_alter().
 */
function ef_views_data_alter(array &$data) {
  $data['embeddable_field_data']['embeddable_in_wysiwyg'] = [
    'title' => t('Is embeddable in WYSIWYG'),
    'filter' => [
      'title' => t('Is embeddable in WYSIWYG'),
      'help' => t('Filters based on whether the embeddable type is available for embedding into a WYSIWYG. This is determined by checking whether a entity embed view mode was created for the embeddable type..'),
      'field' => 'type',
      'id' => 'ef_embeddable_in_wysiwyg'
    ],
  ];
}