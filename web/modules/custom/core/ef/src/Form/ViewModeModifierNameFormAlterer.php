<?php


namespace Drupal\ef\Form;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Form\FormStateInterface;

class ViewModeModifierNameFormAlterer {
  public function alterSettingsForm (&$form, EntityViewDisplayInterface  $entity_view_display, $view_mode, FormStateInterface $form_state) {
    $form['view_mode_modifier_name'] = [
      '#type' => 'textfield',
      '#title' => t('View mode modifier name'),
      '#description' => t('Use this field to pass a modifier through based on this view mode. This is only required if different view modes on this type use the same pattern and need the view mode information to cause a visual change. This is best left blank unless you specifically need it. You only need to provide the modifier part of the class name as the system will prepend the block/component name. '),
      '#default_value' => $entity_view_display->getThirdPartySetting('ef', 'view_mode_modifier_name'),
      '#empty_value' => '_none',
      '#weight' => 0,
    ];
  }
}