<?php

namespace Drupal\ef\Element;

use Drupal\Core\Render\Element\RenderElement;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ef\Decorator\HTMLClassDecoratorFactoryInterface;
use Drupal\ef\EmbeddableInterface;
use Drupal\ef\EmbeddableMode;
use Drupal\ef\EmbeddableReferenceModeInterface;
use Drupal\ef\EmbeddableViewBuilderInterface;
use Drupal\ef\EmbeddableViewModeHelperTrait;
use Drupal\ef\Entity\Embeddable;
use Drupal\ef\Plugin\EmbeddableReferenceOptionsPluginInterface;
use Drupal\ef\RequiresEmbeddableReferenceOptionTrait;

/**
 * Provides a render element to an embeddable, along with titles, descriptions
 * etc.
 *
 * @RenderElement("embeddable")
 */
class EmbeddableElement extends RenderElement {
  use RequiresEmbeddableReferenceOptionTrait;

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return [
      '#pre_render' => [
        [$this, 'preRenderEmbeddableElement'],
      ],
      '#header_title' => NULL,
      '#header_description' => NULL,
      '#embeddable_id' => '',
      '#view_mode' => 'default',
      '#theme' => 'embeddable',
      '#mode' => NULL,
      '#options' => [],
    ];
  }

  /**
   * Embeddable prerender callback
   *
   * @param array $element The render array
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function preRenderEmbeddableElement(array $element) {

    /** @var $embeddableReferenceModeInterface EmbeddableReferenceModeInterface */
    $embeddableReferenceModeInterface = \Drupal::service('ef.embeddable_reference_mode');

    if (!isset($element['#mode'])) {
      $element['#mode'] = $embeddableReferenceModeInterface->getDefaultMode();
    }

    $mode = $element['#mode'];
    $element['#access'] = $embeddableReferenceModeInterface->getAccess($mode);

    if (!$element['#access']) {
      // let's short-cut the build process if access is denied.
      return [];
    }

    if (!isset($element['#embeddable'])) {
      $element['#embeddable'] = Embeddable::load($element['#embeddable_id']);
      unset($element['#embeddable_id']);
    }

    $embeddable = $element['#embeddable'];
    $embeddable_bundle = $embeddable->bundle();
    $view_mode = $element['#view_mode'];

    /** @var \Drupal\ef\EmbeddableViewBuilderInterface $view_builder */
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder($element['#embeddable']->getEntityTypeId());

    $embeddableReferenceOptionPlugins = $this->getEnabledReferenceOptions ($embeddable_bundle, $view_mode);

    $embeddable_reference_build_options = [
      'section_heading_title' => $element['#header_title'],
      'section_heading_description' => $element['#header_description'],
    ];

    $view_mode_modifier_name = $this->getThirdPartySettingForEmbeddableBundleAndViewMode ($embeddable_bundle, $view_mode, 'view_mode_modifier_name');

    if ($view_mode_modifier_name && strlen($view_mode_modifier_name) > 0) {
      $embeddable_reference_build_options['view_mode_modifier_name'] = $view_mode_modifier_name;
    }

    /** @var EmbeddableReferenceOptionsPluginInterface $embeddableReferenceOptionPlugin */
    foreach ($embeddableReferenceOptionPlugins as $embeddableReferenceOptionPlugin) {
      $pluginId = $embeddableReferenceOptionPlugin->getId();

      if (isset($element['#options'][$pluginId])) {
        $options = $element['#options'][$pluginId];
        $plugin_value = $embeddableReferenceOptionPlugin->getOptionValue($options);

        if (!is_null($plugin_value)) {
          $embeddable_reference_build_options[$pluginId] = $plugin_value;
        }
      }
    }

    $element['embeddable_content'] = $view_builder->viewEmbeddable($embeddable, $embeddable_reference_build_options, $view_mode, []);

    //$element['#attached']['library'][] = sprintf('ef/embeddable.%s.%s', $embeddable_bundle, $view_mode);

    return $element;
  }

}