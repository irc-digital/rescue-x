<?php

namespace Drupal\Tests\ef\Unit\Entity;

use Drupal\Core\Form\FormState;
use Drupal\ef\Element\EmbeddingOptionsElement;
use Drupal\KernelTests\KernelTestBase;

/**
 * Class EmbeddingOptionsElement
 * @package Drupal\Tests\ef\Unit\Entity
 *
 * @group ef
 * @coversDefaultClass \Drupal\ef\Element\EmbeddingOptionsElement
 */
class EmbeddingOptionsElementTest extends KernelTestBase {
  public static $modules = ['system', 'field', 'image', 'media', 'file', 'text', 'language', 'content_translation', 'user', 'filter', 'crop', 'image_widget_crop', 'node', 'ds', 'paragraphs', 'ef', 'ef_test'];

  public function setUp() {
    parent::setUp();

    $this->installConfig(['language','ef', 'ef_test']);
  }

  /**
   * @covers ::processEmbeddingOptionsElement
   * @covers ::getEmbeddableViewModeVisibilityService
   */
  public function testProcessEmbeddingOptionsElement () {
    /** @var \Drupal\Core\Render\ElementInfoManager $elementPluginManager */
    $elementPluginManager = \Drupal::service('plugin.manager.element_info');

    /** @var EmbeddingOptionsElement $embeddingOptionsElement */
    $embeddingOptionsElement = $elementPluginManager->createInstance('embedding_options');

    $element = $embeddingOptionsElement->getInfo();

    $element = [
        '#bundle' => 'test',
        '#parents' => ['boss']
      ] + $element;

    $form = [];
    $form_state = new FormState();

    $output = $embeddingOptionsElement->processEmbeddingOptionsElement($element, $form_state, $form);

    if (isset($output['options']['embeddable_count_option'])) {
      unset($output['options']['embeddable_count_option']);
    }

    $expected_options = [
      '#type' => 'container',
      '#weight' => 30,
      '#tree' => true,
      'embeddable_test_options' => [
        '#type' => 'radios',
        '#title' => 'My options',
        '#options' => [
            'option_one' => 'One',
            'option_two' => 'Two',
            'option_three' => 'Three',
        ],
        '#required' => true,
        '#default_value' => ['option_one'],
      ],
    ];

    $this->assertEquals($expected_options, $output['options']);

    $expected_mode_element = [
      '#type' => 'radios',
      '#title' => 'Mode',
      '#required' => true,
      '#options' => [
        'enabled' => 'Enabled',
        'test' => 'Test',
        'disabled' => 'Disabled',
      ],
      '#default_value' => 'enabled',
      '#weight' => 10,
    ];

    $this->assertEquals($expected_mode_element, $output['mode']);

  }

  public function testProcessEmbeddingOptionsElementNoMode () {
    /** @var \Drupal\Core\Render\ElementInfoManager $elementPluginManager */
    $elementPluginManager = \Drupal::service('plugin.manager.element_info');

    /** @var EmbeddingOptionsElement $embeddingOptionsElement */
    $embeddingOptionsElement = $elementPluginManager->createInstance('embedding_options');

    $element = $embeddingOptionsElement->getInfo();

    $element = [
        '#bundle' => 'test',
        '#parents' => ['boss'],
        '#view_mode_editable' => FALSE,
      ] + $element;

    $form = [];
    $form_state = new FormState();

    $output = $embeddingOptionsElement->processEmbeddingOptionsElement($element, $form_state, $form);

    $this->assertFalse(isset($output['view_mode']));
    $this->assertTrue(isset($output['options']));
  }
}