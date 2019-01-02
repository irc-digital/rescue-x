<?php

namespace Drupal\ef\Plugin\DsField;

use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\views\Views;

/**
 * Defines a DsField that will output a fairly basic view - the type we use on
 * some embeddables whereby the content is filtered by the embeddable's parent.
 *
 * Take a look at @see ef_dynamic_content_views_field_info
 *
 * @DsField(
 *   id = "embeddable_views_field",
 *   deriver = "Drupal\ef\Plugin\Derivative\EmbeddableViewsFieldDeriver"
 * )
 *
 */
class EmbeddableViewsField extends DsFieldBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\ef\EmbeddableInterface $embeddable */
    $embeddable = $this->entity();
    $config = $this->getConfiguration();

    $parent_entity = $embeddable->getParentEntity();

    $view_arguments = [$parent_entity];

    if (isset($config["field"]["view"]["arguments"]) && count($config["field"]["view"]["arguments"]) > 0) {
      foreach ($config["field"]["view"]["arguments"] as $view_argument_option_name => $view_argument_option_default_value) {
        $embedding_options = $config["build"]["#embeddable_reference_options"];
        $view_arguments[] = isset($embedding_options[$view_argument_option_name]) ? $embedding_options[$view_argument_option_name] : $view_argument_option_default_value;
      }
    }

    $view = Views::getView($config["field"]["view"]["name"]);
    $view->setArguments($view_arguments);
    $view->setDisplay($config["field"]["view"]["display"]);
    $view->preExecute();
    $view->execute($config["field"]["view"]["display"]);
    $content = $view->buildRenderable($config["field"]["view"]["display"], $view_arguments);

    return [
      '#ef_ds_custom_field_element' => TRUE,
      '#markup' => [
        $config["field"]["field_name"] => [
          $content,
        ],
      ],
    ];
  }
}
