<?php

namespace Drupal\ef;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\PrivateKey;
use Drupal\Core\Site\Settings;
use Drupal\Core\Theme\Registry;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\ef\Decorator\HTMLClassDecoratorFactoryInterface;
use Drupal\ef\Decorator\HTMLComponentDecorator;
use Drupal\ef\Entity\EmbeddableType;
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

  /** @var ThemeManagerInterface */
  protected $themeManager;

  /** @var \Drupal\Core\PrivateKey */
  protected $privateKey;

  public function __construct(EntityTypeInterface $entity_type, EntityManagerInterface $entity_manager, LanguageManagerInterface $language_manager, Registry $theme_registry = NULL, HTMLClassDecoratorFactoryInterface $embeddableDecorator, ThemeManagerInterface $themeManager, PrivateKey $private_key) {
    parent::__construct($entity_type, $entity_manager, $language_manager, $theme_registry, $embeddableDecorator);
    $this->embeddableDecorator = $embeddableDecorator;
    $this->themeManager = $themeManager;
    $this->privateKey = $private_key;
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
      $container->get('ef.html_class_decorator.factory'),
      $container->get('theme.manager'),
      $container->get('private_key')
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

    if ($build["#view_mode"] == 'entity_embed') {
      $embeddable_reference_options[] = 'entity-embed';
    }

    $build['#embeddable_reference_options'] = $embeddable_reference_options;

    $this->buildContextualMenu($build, $embeddable, $embeddable_reference_options, $view_mode);

    $this->addModifierCacheKey($build, $embeddable, $embeddable_reference_options, $view_mode);
    return $build;
  }

  /**
   * The same embeddable can be rendered with different reference options (modifiers) so we need to make sure
   * that caching considers the reference options as key
   *
   * @param $build
   * @param \Drupal\ef\EmbeddableInterface $embeddable
   * @param array $embeddable_reference_options
   * @param $view_mode
   */
  protected function addModifierCacheKey (&$build, EmbeddableInterface $embeddable, array $embeddable_reference_options = [], $view_mode) {
    $serialized_options = serialize($embeddable_reference_options);
    $options_hash = hash('sha256', $this->privateKey->get() . Settings::getHashSalt() . $serialized_options);
    $build['#cache']['keys'][] = 'embeddable_modifiers_hash:' . $options_hash;

  }

  protected function buildContextualMenu (&$build, EmbeddableInterface $embeddable, array $embeddable_reference_options = [], $view_mode) {

    /** @var \Drupal\Core\Theme\ActiveTheme $active_theme */
    $active_theme = $this->themeManager->getActiveTheme();

    if ($active_theme->getName() == 'seven') {
      return;
    }

    $destination = \Drupal::destination()->getAsArray();

    $current_language = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT);

    $contextual_menu_links = [];

    $link_or_translate_link = NULL;

    $contextual_entity = $embeddable;
    $this->moduleHandler()->alter('embeddable_contextual_entity_' . $contextual_entity->bundle(), $contextual_entity, $view_mode);

    if ($contextual_entity->hasTranslation($current_language->getId())) {
      $edit_link = $contextual_entity->toUrl('edit-form', ['language' => $current_language, 'query' => $destination]);

      if ($edit_link->access()) {
        $link_or_translate_link = [
          'url' => $edit_link->toString(),
          'title' => $this->t('Edit'),
        ];

        $contextual_menu_links[] = $link_or_translate_link;

      }
    } else {
      $translate_link = $contextual_entity->toUrl('drupal:content-translation-add', [
        'language' => $current_language,
        'query' => $destination
      ]);
      $translate_link->setRouteParameter('source', $contextual_entity->language()
        ->getId());
      $translate_link->setRouteParameter('target', $current_language->getId());

      if ($translate_link->access()) {
        $link_or_translate_link = [
          'url' => $translate_link->toString(),
          'title' => $this->t('Translate'),
        ];

        $contextual_menu_links[] = $link_or_translate_link;
      }
    }

    $manage_translations_link = $contextual_entity->toUrl('drupal:content-translation-overview', ['language' => $current_language, 'query' => $destination]);

    if ($manage_translations_link->access()) {
      $contextual_menu_links[] = [
        'url' => $manage_translations_link->toString(),
        'title' => $this->t('Manage translations'),
      ];
    }

    if (sizeof($contextual_menu_links) > 0) {
      $build['#ef_contextual_menu'] = [
        '#type' => 'pattern',
        '#id' => 'contextual_menu',
        '#fields' => [
          'contextual_menu_items' => $contextual_menu_links,
        ],
        '#cache' => [
          'contexts' => ['user.permissions'],
          'keys' => ['ef_contextual_menu', 'embeddable', $embeddable->id(), $view_mode],
          'tags' => $embeddable->getCacheTags(),
        ],
      ];

    }
  }
}
