<?php

namespace Drupal\Tests\ef_wysiwyg_embed\Kernel;

use Drupal\ef\Entity\Embeddable;
use Drupal\ef_wysiwyg_embed\Plugin\EmbeddableViewModeVisibility\EmbeddableViewModeVisibilityWysiwyg;
use Drupal\KernelTests\KernelTestBase;

class WysiwygEmbedTest extends KernelTestBase  {
  public static $modules = ['user', 'ef', 'ef_wysiwyg_embed', 'ef_wysiwyg_embed_test'];

  public function setUp() {
    parent::setUp();

    $this->installConfig(['ef', 'ef_wysiwyg_embed_test']);
  }

  /**
   * @covers \Drupal\ef_wysiwyg_embed\Plugin\EmbeddableViewModeVisibility\EmbeddableViewModeVisibilityWysiwyg
   */
  public function testVisibilityOptions () {
    /** @var \Drupal\ef\EmbeddableViewModeVisibility $viewModeVisibilityService */
    $viewModeVisibilityService = \Drupal::service('ef.view_mode_visibility');
    $viewModes = $viewModeVisibilityService->getVisibleViewModes('wysiwyg_test', EmbeddableViewModeVisibilityWysiwyg::class);
    $this->assertArrayHasKey('default', $viewModes);
    $this->assertArrayNotHasKey('ef_variation_1', $viewModes);
    $this->assertArrayHasKey('ef_variation_2', $viewModes);
  }

  /**
   * This checks that the assumptiosn we made abouyt the entity embed module
   * continue to hold as that module evolves.
   *
   * @see ef_wysiwyg_embed_entity_embed_display_plugins_for_context_alter
   */
  public function testEntityEmbedDisplayManagerForEmbeddables () {
    $this->enableModules(['entity_embed']);

    /** @var \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager $plugin_manager */
    $plugin_manager = \Drupal::service('plugin.manager.entity_embed.display');

    $test_embeddable = Embeddable::create([
      'type' => 'test',
    ]);
    $plugins = $plugin_manager->getDefinitionsForContexts(['entity_type' => 'embeddable', 'entity' => $test_embeddable]);

    $this->assertTrue(count($plugins) === 1);

    $key = key($plugins);

    $this->assertEquals('entity_reference_embeddable_entity_display:entity_reference_embeddable_view', $key);

  }

  /**
   * @covers \Drupal\ef_wysiwyg_embed\Plugin\entity_embed\EntityEmbedDisplay\EmbeddableFieldFormatter::getFieldDefinition
   */
  public function testEmbeddableFieldFormatter () {
    $this->enableModules(['entity_embed']);

    /** @var \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager $plugin_manager */
    $plugin_manager = \Drupal::service('plugin.manager.entity_embed.display');

    /** @var \Drupal\ef_wysiwyg_embed\Plugin\entity_embed\EntityEmbedDisplay\EmbeddableFieldFormatter $embeddable_field_formatter */
    $embeddable_field_formatter = $plugin_manager->createInstance('entity_reference_embeddable_entity_display:entity_reference_embeddable_view');

    $this->assertNotNull($embeddable_field_formatter);

    $test_embeddable = Embeddable::create([
      'type' => 'test',
    ]);

    $embeddable_field_formatter->setContextValue('entity_type', 'embeddable');
    $embeddable_field_formatter->setContextValue('entity', $test_embeddable);

    /** @var \Drupal\Core\Field\BaseFieldDefinition $field_definition */
    $field_definition = $embeddable_field_formatter->getFieldDefinition();

    $usage = $field_definition->getSetting('ef_view_mode_visibility_usage_context');

    $this->assertNotNull($usage);
    $this->assertEquals(EmbeddableViewModeVisibilityWysiwyg::class, $usage);

  }

}