<?php

namespace Drupal\ef_mandatory_field_summary;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MandatoryFieldSummaryHelper implements ContainerInjectionInterface {
  use StringTranslationTrait;

  public function __construct(TranslationInterface $translation) {
    $this->setStringTranslation($translation);
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('string_translation')
    );
  }

  public function modifyFieldSettingsForm (WidgetInterface $plugin, FieldDefinitionInterface $field_definition, $form_mode, $form, FormStateInterface $form_state) {
    $element['textarea_summary_required'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Summary is required'),
      '#description' => $this->t('When checked the summary field will be made visible by default and will be a mandatory field.'),
      '#default_value' => $plugin->getThirdPartySetting('ef_mandatory_field_summary', 'textarea_summary_required'),
    ];
    return $element;
  }

  public function generateFieldSettingSummary (&$summary, $context) {
    /** @var \Drupal\text\Plugin\Field\FieldWidget\TextareaWithSummaryWidget $plugin */
    $plugin = $context['widget'];

    $textarea_summary_required = $plugin->getThirdPartySetting('ef_mandatory_field_summary', 'textarea_summary_required');
    if ($textarea_summary_required) {
      $summary[] = $this->t('Summary field required');
    }
    return $summary;
  }

  public function textareaFormFieldAlter (&$element, FormStateInterface $form_state, $context) {
    /** @var \Drupal\text\Plugin\Field\FieldWidget\TextareaWithSummaryWidget $plugin */
    $plugin = $context['widget'];

    $textarea_summary_required = $plugin->getThirdPartySetting('ef_mandatory_field_summary', 'textarea_summary_required');

    if ($textarea_summary_required) {
      $element['summary']['#required'] = TRUE;
      $element['summary']['#prefix'] = $element['summary']['#suffix'] = NULL;
      $element['summary']['#description'] = $this->t('This summary can be used in teasers.');

      if (($key = array_search('js-text-summary', $element['summary']['#attributes']['class'])) !== false) {
        unset($element['summary']['#attributes']['class'][$key]);
      }
    }
  }
}