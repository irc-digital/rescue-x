<?php

namespace Drupal\ef_patterns\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * @Filter(
 *   id = "filter_pattern_decorator",
 *   title = @Translation("Pattern class decorator"),
 *   description = @Translation("Decorates basic HTML elements, like paragraphs with a Rescue Pattern Library pattern class"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 *   weight = 10
 * )
 */
class RichTextContentPatternDecorator extends FilterBase {
  public function process($text, $langcode) {

    $result = $text;

    $config_text = $this->settings['config'];

    $config_row_array = explode("\r\n", $config_text);

    if (count($config_row_array) > 0) {
      /** @var \DOMDocument $dom_document */
      $dom_document = Html::load($text);

      foreach ($config_row_array as $config_row) {
        $row_values = explode('|', $config_row);

        $entry_count = count($row_values);

        if ($entry_count > 1 && $entry_count <= 4) {
          $html_element = $row_values[0];
          $bem_block = $row_values[1];
          $bom_modifiers = $entry_count > 2 ? $row_values[2] : FALSE;
          $bom_first_modifiers = $entry_count == 4 ? $row_values[3] : FALSE;

          /** @var \DOMNodeList $nodeList */
          $node_list = $this->getChildElementsByTagName($dom_document, $html_element);

          /** @var \DOMElement $node */
          foreach ($node_list as $key => $node) {
            $class = '';

            if ($node->hasAttribute('class')) {
              $class = $node->getAttribute('class') . ' ';
            }

            $class .= $bem_block;

            if ($bom_modifiers) {
              $class .= ' ' . $this->getModifiers($bem_block, $bom_modifiers);
            }

            if ($bom_first_modifiers && $key == key($node_list)) {
              $class .= ' ' . $this->getModifiers($bem_block, $bom_first_modifiers);
            }

            $node->setAttribute('class', $class);
          }

        }

      }

      $result = Html::serialize($dom_document);
    }

    return new FilterProcessResult($result);
  }

  /**
   * Only looks at the child elements of the body tag for elements that match the supplied html_element
   *
   * @param \DOMDocument $dom_document
   * @param $html_element
   * @return array
   */
  protected static function getChildElementsByTagName (\DOMDocument $dom_document, $html_element) {
    $node_list = $dom_document->getElementsByTagName('body');

    $result = [];

    if ($node_list->length == 1) {
      /** @var \DOMNode $body_node */
      $body_node = $node_list[0];

      /** @var \DOMElement $childNode */
      foreach ($body_node->childNodes as $childNode) {
        if ($childNode->tagName == $html_element) {
          $result[] = $childNode;
        }
      }
    }

    return $result;

  }

  protected function getModifiers ($bem_block, $modifier_config_string) {
    $modifiers = explode(' ', $modifier_config_string);
    $modifiers = preg_filter('/^/', $bem_block . '--', $modifiers);
    return implode(' ', $modifiers);
  }

  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['config'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('HTML tag config'),
      '#default_value' => $this->settings['config'],
      '#description' => $this->t('Place the configuration for each tag on a separate line. The format of the line is pipe separated. The first element on the line is the HTML tag. The second element is the block or component name (in BEM speak). The third element is optional and contains a space-separated list of modifiers to be applied to the block class. The third element is also optional and is the modifier to be placed on the very first block of that type found.'),
    );
    return $form;
  }
}