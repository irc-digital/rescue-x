<?php

namespace Drupal\Tests\ef\Plugin;

use Drupal\Core\Form\FormState;
use Drupal\ef\Entity\EmbeddableOption;
use Drupal\ef\Plugin\EmbeddableReferenceOptionsPluginManager;
use Drupal\KernelTests\KernelTestBase;

/**
 * Class EmbeddableReferenceOptionsPluginManagerTest
 * @package Drupal\Tests\ef\Plugin
 * @coversDefaultClass \Drupal\ef\Plugin\EmbeddableReferenceOptionsPluginManager
 *
 * @group ef
 */
class EmbeddableReferenceOptionsPluginManagerTest extends KernelTestBase {
  public static $modules = ['user', 'ef', 'ef_test'];

  /**
   * Checks that if we register a legit plugin that it appears in the list
   *
   * @covers ::__construct
   */
  public function testGlue () {
    /** @var EmbeddableReferenceOptionsPluginManager $embeddableReferenceOptionsPluginManager */
    $embeddableReferenceOptionsPluginManager = \Drupal::service('plugin.manager.embeddable_reference_options');

    $definitions = $embeddableReferenceOptionsPluginManager->getDefinitions();
    $this->assertTrue(count($definitions) > 0);
  }

  public function testCreatingDefaultPlugin () {
    /** @var EmbeddableReferenceOptionsPluginManager $embeddableReferenceOptionsPluginManager */
    $embeddableReferenceOptionsPluginManager = \Drupal::service('plugin.manager.embeddable_reference_options');

    $definitions = $embeddableReferenceOptionsPluginManager->getDefinitions();

    $this->assertArrayHasKey('embeddable_test_options', $definitions);

    /** @var \Drupal\ef\Plugin\EmbeddableReferenceOptionsPluginInterface $plugin */
    $plugin = $embeddableReferenceOptionsPluginManager->createInstance('embeddable_test_options');

    $form = $plugin->buildForm('test', []);

    $this->assertEquals('option_one', $form['#default_value'][0]);
  }

  public function testCreatingConfiguredPlugin () {
    /** @var EmbeddableReferenceOptionsPluginManager $embeddableReferenceOptionsPluginManager */
    $embeddableReferenceOptionsPluginManager = \Drupal::service('plugin.manager.embeddable_reference_options');

    $form_option = 'option_two';

    $configuration = ['default_selected_button' => $form_option];


    $definitions = $embeddableReferenceOptionsPluginManager->getDefinitions();

    $this->assertArrayHasKey('embeddable_test_options', $definitions);

    /** @var \Drupal\ef\Plugin\EmbeddableReferenceOptionsPluginInterface $plugin */
    $plugin = $embeddableReferenceOptionsPluginManager->createInstance('embeddable_test_options', $configuration);

    $form = $plugin->buildForm('test', []);

    $this->assertEquals($form_option, $form['#default_value'][0]);
  }

  /**
   * @covers \Drupal\ef\Plugin\EmbeddableReferenceOptions\CountOption::buildForm
   * @covers \Drupal\ef\Plugin\EmbeddableReferenceOptions\CountOption::buildConfigurationForm
   * @covers \Drupal\ef\Plugin\EmbeddableReferenceOptions\CountOption::parsePermittedValues
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function testCountConfiguredPlugin () {
    /** @var EmbeddableReferenceOptionsPluginManager $embeddableReferenceOptionsPluginManager */
    $embeddableReferenceOptionsPluginManager = \Drupal::service('plugin.manager.embeddable_reference_options');

    $permitted_values = '1,2,3';
    $default_value = 3;

    $configuration = [
      'permitted_values' => $permitted_values,
      'default_value' => $default_value,
    ];

    /** @var \Drupal\ef\Plugin\EmbeddableReferenceOptionsPluginInterface $plugin */
    $plugin = $embeddableReferenceOptionsPluginManager->createInstance('embeddable_count_option', $configuration);

    $admin_form = [];
    $form_state = new FormState();
    $admin_form = $plugin->buildConfigurationForm($admin_form, $form_state);

    $this->assertEquals($permitted_values, $admin_form['permitted_values']['#default_value']);
    $this->assertEquals($default_value, $admin_form['default_value']['#default_value']);

    $editor_form = $plugin->buildForm('test', []);

    $expected = [
      'count' => [
        '#type' => 'select',
        '#options' => ['1' => '1', '2' => '2', '3' => '3'],
        '#title' => 'Count',
        '#default_value' => 3,
        '#required' => true,
      ],
    ];

    $this->assertEquals($expected, $editor_form);

    $plugin->setConfiguration(['default_value' => '3', 'permitted_values' => '[1-5]']);
    $editor_form = $plugin->buildForm('test', []);


  }
}
