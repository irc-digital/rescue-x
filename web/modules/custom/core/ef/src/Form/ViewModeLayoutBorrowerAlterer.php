<?php


namespace Drupal\ef\Form;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ef\EmbeddableViewModeHelperTrait;

class ViewModeLayoutBorrowerAlterer {
  use EmbeddableViewModeHelperTrait;

  public function alterSettingsForm (&$form, EntityViewDisplayInterface  $entity_view_display, $view_mode, FormStateInterface $form_state) {
    $view_modes = ['none' => t('-- Do not borrow layout --')] + $this->getEmbeddableViewModes($entity_view_display->getTargetBundle());

    unset($view_modes[$view_mode]);

    if (count($view_modes) > 0) {
      $default_value = $form_state->getValue('borrowed_layout', $entity_view_display->getThirdPartySetting('ef', 'borrowed_layout'));

      $form['borrowed_layout'] = [
        '#type' => 'select',
        '#options' => $view_modes,
        '#title' => t('Borrow layout from'),
        '#description' => t("Sometimes you may need the exact same HTML/layout but end up having to create a new view mode just to support different options. In that case you can use this field to borrow another view mode's layout. As this is just sharing the HTML, and to provide as much styling flexibility as possible, the view mode on the outer embeddable element will remain this view mode. This will allow you to handle the CSS as best fits the use-case."),
        '#default_value' => $default_value,
        '#weight' => 0,
      ];
    }
  }
}