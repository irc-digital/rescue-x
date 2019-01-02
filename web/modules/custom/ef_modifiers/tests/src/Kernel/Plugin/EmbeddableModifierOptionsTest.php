<?php

namespace Drupal\Tests\ef_modifiers\Plugin;

use Drupal\Core\Form\FormState;
use Drupal\ef\Entity\Embeddable;
use Drupal\ef\Entity\EmbeddableType;
use Drupal\ef\Plugin\EmbeddableReferenceOptionsPluginManager;
use Drupal\ef_modifiers\Entity\EmbeddableModifier;
use Drupal\KernelTests\KernelTestBase;
use Drupal\ef\Plugin\EmbeddableReferenceOptionsPluginInterface;

/**
 * Class EmbeddableModifierOptionsTest
 *
 * @package Drupal\Tests\ef\Plugin
 * @coversDefaultClass \Drupal\ef_modifiers\Plugin\EmbeddableReferenceOptions\EmbeddableModifierOptions
 *
 * @group ef
 */
class EmbeddableModifierOptionsTest extends KernelTestBase {
  public static $modules = ['user', 'ef', 'ef_modifiers', 'ef_test', 'ef_modifiers_test'];

  public function setUp() {
    parent::setUp();

    $this->installConfig(['ef', 'ef_modifiers', 'ef_test', 'ef_modifiers_test']);
  }

  /**
   * Checks that if we register a legit plugin that it appears in the list
   *
   * @covers ::getId
   * @covers ::__construct
   * @covers ::create
   */
  public function testGetId () {
      /** @var EmbeddableReferenceOptionsPluginManager $embeddableReferenceOptionsPluginManager */
    $embeddableReferenceOptionsPluginManager = \Drupal::service('plugin.manager.embeddable_reference_options');

    $definitions = $embeddableReferenceOptionsPluginManager->getDefinitions();

    $this->assertTrue(count($definitions) > 0);

    $this->assertTrue(isset($definitions['embeddable_modifier_options']));

    $modifierDef = $definitions['embeddable_modifier_options'];

    /** @var EmbeddableReferenceOptionsPluginInterface $modifier */
    $modifier = $embeddableReferenceOptionsPluginManager->createInstance($modifierDef['id']);

    $this->assertTrue('embeddable_modifier_options', $modifier->getId());
  }

  /**
   * @covers \Drupal\ef_modifiers\Entity\EmbeddableModifier::load
   * @covers \Drupal\ef_modifiers\Entity\EmbeddableModifier::getOptions
   * @covers \Drupal\ef_modifiers\Entity\EmbeddableModifier::getDefaultOption
   * @covers \Drupal\ef_modifiers\Entity\EmbeddableModifier::getDefaultOptionObject
   * @covers \Drupal\ef_modifiers\Entity\EmbeddableModifier::getClassName
   * @covers \Drupal\ef_modifiers\Entity\EmbeddableModifier::getDescription
   * @covers \Drupal\ef_modifiers\Entity\EmbeddableModifier::getTooltip
   * @covers \Drupal\ef_modifiers\Entity\EmbeddableModifierOption::id
   * @covers \Drupal\ef_modifiers\Entity\EmbeddableModifierOption::label
   * @covers \Drupal\ef_modifiers\Entity\EmbeddableModifierOption::getClassName
   * @covers \Drupal\ef_modifiers\Entity\EmbeddableModifierOption::getFullClassName
   */
  public function testModifierAndOption () {
    $boxModifier = EmbeddableModifier::load('box_color');

    $this->assertNotNull($boxModifier);

    $this->assertEquals('box-color', $boxModifier->getClassName());

    $this->assertEquals('What color should the box be?', $boxModifier->getDescription());

    $this->assertEquals('Box tooltip', $boxModifier->getTooltip());

    $this->assertEquals('Box color editorial name', $boxModifier->getEditorialName());

    $this->assertEquals('Box color', $boxModifier->getAdministrativeName());

    /** @var \Drupal\ef_modifiers\Entity\EmbeddableModifierOption $defaultOption */
    $defaultOption = $boxModifier->getDefaultOptionObject();

    $this->assertEquals('box_color.yellow', $defaultOption->id());

    $this->assertEquals('yellow', $defaultOption->getClassName());

    $this->assertEquals('box-color-yellow', $defaultOption->getFullClassName());

    $this->assertEquals('Yellow', $defaultOption->label());

    /** @var \Drupal\ef_modifiers\Entity\EmbeddableModifierOption[] $boxModifierOptions */
    $boxModifierOptions = $boxModifier->getOptions();

    $this->assertEquals(count($boxModifierOptions), 4);
  }

  /**
   * @covers ::buildForm
   */
  public function testBuildForm () {
    /** @var EmbeddableReferenceOptionsPluginManager $embeddableReferenceOptionsPluginManager */
    $embeddableReferenceOptionsPluginManager = \Drupal::service('plugin.manager.embeddable_reference_options');

    $definitions = $embeddableReferenceOptionsPluginManager->getDefinitions();

    $modifierDef = $definitions['embeddable_modifier_options'];

    /** @var EmbeddableReferenceOptionsPluginInterface $modifier */
    $modifier = $embeddableReferenceOptionsPluginManager->createInstance($modifierDef['id'], ['enabled_modifiers' => ['box_color', 'background_color']]);

    $testEmbeddableType = EmbeddableType::load('test');

    $options = [
      'background_color' => 'background_color.red',
    ];

    $form = $modifier->buildForm($testEmbeddableType->id(), $options);

    // check the box_color
    $box_color = $form['box_color'];

    $box_expectation = [
      '#type' => 'radios',
      '#title' => 'Box color editorial name',
      '#required' => TRUE,
      '#options' => [
        'box_color.blue' => 'Blue',
        'box_color.green' => 'Green',
        'box_color.red' => 'Red',
        'box_color.yellow' => 'Yellow',
      ],
      '#default_value' => 'box_color.yellow',
    ];

    $this->assertEquals($box_expectation, $box_color);

    // check the background_color
    $background_color = $form['background_color'];

    $background_expectation = [
      '#type' => 'select',
      '#title' => 'Background color editorial name',
      '#required' => TRUE,
      '#options' => [
        'background_color.blue' => 'Blue',
        'background_color.white' => 'White',
        'background_color.red' => 'Red',
        'background_color.yellow' => 'Yellow',
        'background_color.purple' => 'Purple',
      ],
      '#default_value' => 'background_color.red',
    ];

    $this->assertEquals($background_expectation, $background_color);
  }

}
