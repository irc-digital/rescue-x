<?php


namespace Drupal\ef\Form;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Form\FormStateInterface;

class EditorFriendlyNameFormAlterer {
  public function alterSettingsForm (&$form, EntityViewDisplayInterface  $entity_view_display, $view_mode, FormStateInterface $form_state) {
    $form['editor_friendly_name'] = [
      '#type' => 'textfield',
      '#title' => t('Editor-friendly view mode name'),
      '#description' => t('By default the embeddable framework provides generic view mode names. Providing an editor-friendly view mode name will help them choose the proper variation when adding embeddables across the site.'),
      '#default_value' => $entity_view_display->getThirdPartySetting('ef', 'editor_friendly_name'),
      '#empty_value' => '_none',
      '#weight' => 0,
    ];
  }
}