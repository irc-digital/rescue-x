<?php

use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\ef\EmbeddableInterface;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Hook to allow modules to jump in and add settings to the admin's view mode
 * embeddable framework section.
 *
 * NOTE: When you add in an element to the form you should include a #config
 * element. This should contain the subpath of the config entry that has been
 * defind to store your entries.
 *
 * @see ef_ef_view_mode_settings
 *
 * @param array $form The subset of the form that is the EF option's tab
 * @param EntityFormDisplayInterface $entity_view_display The view display
 * @param string $view_mode The machine name of the view mode
 */
function hook_ef_view_mode_settings (&$form, EntityFormDisplayInterface $entity_view_display, $view_mode) {
  $form['editor_friendly_name'] = [
    '#type' => 'textfield',
    '#title' => t('Editor-friendly view mode name'),
    '#description' => 'By default the embeddable framework provides generic view mode names. Providing an editor-friendly view mode name will help them choose the proper variation when adding embeddables across the site.',
    '#default_value' => $entity_view_display->getThirdPartySetting('ef', 'editor_friendly_name'),
    '#empty_value' => '_none',
    '#weight' => -1,
  ];
}

/**
 * Create a field that is available to the display that when placed on the
 * embeddable will render a view. This is predominately used for the scenario
 * where a view is used to provide a filtered list of items based on the
 * parent entity of the embeddable. For example, with news and features we use
 * a view to display news items that are filtered by, say, topic or country.
 *
 * This hook is just for putting a field on the display, you will still need
 * to do some work on the view itself. See ef_dynamic_content for a good example
 *
 * The array returned must provide for the following:
 *  'id'            a unique identifier for this views field
 *  'embeddable'    the embeddable type that this views field should shown up on
 *  'label'         a label - this is how admins will see it on the display
 *  'view'          an array of the following:
 *    'name'        the name of the view
 *    'display'     which display of the view should be used?
 *    'arguments'   optional. this is an ordered array of key/value pairs that
 *                  give the name of the embeddable option and a default if that
 *                  option is not provided.
 * @return array
 */
function hook_embeddable_views_field_info () {
  $views_field_info[] = [
    'id' => 'dynamic_content_view',
    'embeddable' => 'dynamic_content',
    'label' => 'Dynamic content view',
    'view' => [
      'name' => 'dynamic_content',
      'display' => 'dynamic_content',
      'arguments' => [
        'embeddable_count_option' => 5,
        'embeddable_sticky_option' => NULL,
      ],
    ],
  ];

  return $views_field_info;
}

/**
 * Called when a dependent embeddable is being created. This allows modules
 * a chance to modify the dependent embeddable when it is created. This hook
 * is generated with a BUNDLE function name where the bundle is the embeddable
 * bundle being created.
 *
 * @param $dependent_embeddable
 * @param \Drupal\Core\Entity\ContentEntityInterface $parent_entity
 */
function hook_dependent_embeddable_presave_BUNDLE_alter (EmbeddableInterface $dependent_embeddable, ContentEntityInterface $parent_entity) {
  $dependent_embeddable->setTitle(t('My new title'));
}

/**
 * Called when a dependent embeddable is being created. This allows modules
 * a chance to modify the dependent embeddable when it is created.
 *
 * @param $dependent_embeddable
 * @param \Drupal\Core\Entity\ContentEntityInterface $parent_entity
 */
function hook_dependent_embeddable_presave_alter (EmbeddableInterface $dependent_embeddable, ContentEntityInterface $parent_entity) {
  $dependent_embeddable->setTitle(t('My new title'));
}