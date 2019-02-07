<?php

namespace Drupal\Tests\ef_wysiwyg_embed\Unit\Entity;

use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ef\Element\EmbeddingOptionsElement;
use Drupal\ef_wysiwyg_embed\Plugin\EmbeddableViewModeVisibility\EmbeddableViewModeVisibilityWysiwyg;
use Drupal\KernelTests\KernelTestBase;

/**
 * Class EmbeddingOptionsElementHelperTest
 * @package Drupal\Tests\ef\Unit\Entity
 *
 * @group ef
 * @coversDefaultClass \Drupal\ef_wysiwyg_embed\EmbeddingOptionsElementHelper
 */
class EmbeddingOptionsElementHelperTest extends KernelTestBase {
  public static $modules = ['system', 'field', 'image', 'media', 'file', 'text', 'language', 'content_translation', 'user', 'filter', 'crop', 'image_widget_crop', 'node', 'ds', 'paragraphs', 'ef', 'ef_test', 'ef_wysiwyg_embed', 'ef_wysiwyg_embed_test'];

  public function setUp() {
    parent::setUp();

    $this->installConfig(['language', 'ef', 'ef_test']);
  }

  /**
   * @covers ::processModeForWysiwyg
   * @covers ::alterElementInfo
   */
  public function testProcessEmbeddingOptionsElement () {
    /** @var \Drupal\Core\Render\ElementInfoManager $elementPluginManager */
    $elementPluginManager = \Drupal::service('plugin.manager.element_info');

    $element = $elementPluginManager->getInfo('embedding_options');

    $element = [
        '#bundle' => 'test',
        '#parents' => ['boss'],
        '#array_parents' => [],
      ] + $element;

    /** @var \Drupal\Core\Form\FormBuilder $formBuilder */
    $formBuilder = \Drupal::service('form_builder');

    /** @var FormStateInterface $formState */
    $formState = new FormState();
    $formState->setCompleteForm($element);
    $output = $formBuilder->doBuildForm('not_a_real_form_id', $element,$formState);

    $expected_mode_options = [
      'enabled' => 'Enabled',
      'test' => 'Test',
      'disabled' => 'Disabled',
    ];

    $this->assertEquals($expected_mode_options, $output['mode']['#options']);

    $element['#visibility'] = EmbeddableViewModeVisibilityWysiwyg::class;

    /** @var FormStateInterface $formState */
    $formState = new FormState();
    $formState->setCompleteForm($element);
    $output = $formBuilder->doBuildForm('not_a_real_form_id', $element,$formState);

    // with disabled removed
    $expected_mode_options = [
      'enabled' => 'Enabled',
      'test' => 'Test',
    ];

    $this->assertEquals($expected_mode_options, $output['mode']['#options']);


  }

}