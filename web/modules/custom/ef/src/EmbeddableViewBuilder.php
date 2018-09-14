<?php

namespace Drupal\ef;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Theme\Registry;
use Drupal\ef\Decorator\HTMLClassDecoratorFactoryInterface;
use Drupal\ef\Decorator\HTMLComponentDecorator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EmbeddableViewBuilder
 *
 * @package Drupal\ef
 */
class EmbeddableViewBuilder extends EntityViewBuilder implements EmbeddableViewBuilderInterface {

  use EmbeddableViewModeHelperTrait;

  /** @var  HTMLClassDecoratorFactoryInterface $embeddableDecorator */
  protected $embeddableDecorator;

  public function __construct(EntityTypeInterface $entity_type, EntityManagerInterface $entity_manager, LanguageManagerInterface $language_manager, Registry $theme_registry = NULL, HTMLClassDecoratorFactoryInterface $embeddableDecorator) {
    $this->entityTypeId = $entity_type->id();
    $this->entityType = $entity_type;
    $this->entityManager = $entity_manager;
    $this->languageManager = $language_manager;
    $this->themeRegistry = $theme_registry ?: \Drupal::service('theme.registry');
    $this->embeddableDecorator = $embeddableDecorator;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager'),
      $container->get('language_manager'),
      $container->get('theme.registry'),
      $container->get('ef.html_class_decorator.factory')
    );
  }

  /**
   * @inheritdoc
   */
  public function view(EntityInterface $embeddable, $view_mode = 'full', $langcode = NULL) {
    return $this->viewEmbeddable($embeddable, [], $view_mode, $langcode);
  }

  /**
   * @inheritdoc
   */
  public function viewEmbeddable(EmbeddableInterface $embeddable, array $embeddable_reference_options = [], $view_mode = 'full', $langcode = NULL) {

    $borrowed_layout = $this->getThirdPartySettingForEmbeddableBundleAndViewMode($embeddable->bundle(), $view_mode, 'borrowed_layout');

    if (!is_null($borrowed_layout) && !$borrowed_layout == 'none') {
      $view_mode = $borrowed_layout;
    }

    $build_list = $this->viewMultipleEmbeddables([$embeddable], [$embeddable_reference_options], $view_mode, $langcode);

    // The default ::buildMultiple() #pre_render callback won't run, because we
    // extract a child element of the default renderable array. Thus we must
    // assign an alternative #pre_render callback that applies the necessary
    // transformations and then still calls ::buildMultiple().
    $build = $build_list[0];
    $build['#pre_render'][] = [$this, 'build'];

    return $build;
  }

  public function viewMultipleEmbeddables(array $entities = [], array $embeddable_reference_options = [[]], $view_mode = 'full', $langcode = NULL) {
    $build_list = [
      '#sorted' => TRUE,
      '#pre_render' => [[$this, 'buildMultiple']],
    ];
    $weight = 0;
    $idx = 0;

    foreach ($entities as $key => $entity) {
      // Ensure that from now on we are dealing with the proper translation
      // object.
      $entity = $this->entityManager->getTranslationFromContext($entity, $langcode);

      // Set build defaults.
      $build_list[$key] = $this->getBuildDefaultsForEmbeddable($entity, $embeddable_reference_options[$idx], $view_mode);
      $entityType = $this->entityTypeId;
      $this->moduleHandler()->alter([$entityType . '_build_defaults', 'entity_build_defaults'], $build_list[$key], $entity, $view_mode);

      $build_list[$key]['#weight'] = $weight++;
      $idx++;
    }

    return $build_list;
  }

  protected function getBuildDefaultsForEmbeddable (EmbeddableInterface $embeddable, array $embeddable_reference_options = [], $view_mode) {
    $build = parent::getBuildDefaults($embeddable, $view_mode);

    $build['#theme'] = 'embeddable_content';

    if ($build["#view_mode"] == 'entity_embed') {
      $embeddable_reference_options[] = 'entity-embed';
    }

    $build['#embeddable_reference_options'] = $embeddable_reference_options;

    return $build;
  }
}
