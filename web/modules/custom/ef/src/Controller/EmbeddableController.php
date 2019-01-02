<?php

namespace Drupal\ef\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\ef\EmbeddableInterface;
use Drupal\ef\EmbeddableTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Embeddable routes.
 */
class EmbeddableController extends ControllerBase implements ContainerInjectionInterface {
  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a EmbeddableController object.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(DateFormatterInterface $date_formatter, RendererInterface $renderer) {
    $this->renderer = $renderer;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('renderer')
    );
  }

  /**
   * Displays add content links for available embeddable types.
   *
   * Redirects to embeddable/add/[type] if only one embeddable type is available.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   A render array for a list of the embeddable types that can be added; however,
   *   if there is only one embeddable type defined for the site, the function
   *   will return a RedirectResponse to the embeddable add page for that one embeddable
   *   type.
   */
  public function addPage() {
    $build = [
      '#theme' => 'embeddable_add_list',
      '#cache' => [
        'tags' => $this->entityTypeManager()->getDefinition('embeddable_type')->getListCacheTags(),
      ],
    ];

    $content = [];

    // Only use node types the user has access to.
    foreach ($this->entityTypeManager()->getStorage('embeddable_type')->loadMultiple() as $type) {
      $access = $this->entityTypeManager()->getAccessControlHandler('embeddable')->createAccess($type->id(), NULL, [], TRUE);
      if ($access->isAllowed()) {
        $content[$type->id()] = $type;
      }
      $this->renderer->addCacheableDependency($build, $access);
    }

    // Bypass the node/add listing if only one content type is available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('entity.embeddable.add', ['embeddable_type' => $type->id()]);
    }

    $build['#content'] = $content;

    return $build;
  }

  /**
   * Provides the embeddable submission form.
   *
   * @param \Drupal\ef\EmbeddableTypeInterface $embeddable_type
   *   The embeddable type entity for the embeddable being created.
   *
   * @return array
   *   An embeddable submission form.
   */
  public function addEmbeddable(EmbeddableTypeInterface $embeddable_type) {
    $embeddable = $this->entityTypeManager()->getStorage('embeddable')->create([
      'type' => $embeddable_type->id(),
    ]);

    $form = $this->entityFormBuilder()->getForm($embeddable);

    return $form;
  }


  /**
   * The _title_callback for the entity.embeddable.add route.
   *
   * @param \Drupal\ef\EmbeddableTypeInterface $embeddable_type
   *   The type of embeddable being created.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle(EmbeddableTypeInterface $embeddable_type) {
    return $this->t('Create @name embeddable', ['@name' => $embeddable_type->label()]);
  }

  public function generateUuid () {
    $uuid_service = \Drupal::service('uuid');
    $uuid = $uuid_service->generate();
    return [
      '#markup' => $uuid,
    ];
  }

  public function testPage () {
//    return [
//      '#type' => 'pattern',
//      '#id' => 'image_partial_width_square',
//      '#fields' => [
//        'image_partial_width_square_responsive_image_source_set' => [
//          "https://placem.at/places?w=640&h=640&random=partialwidthsquare 640w",
//          "https://placem.at/places?w=1280&h=1280&random=partialwidthsquare 1280w",
//          "https://placem.at/places?w=1920&h=1920&random=partialwidthsquare 1920w",
//          "https://placem.at/places?w=2400&h=2400&random=partialwidthsquare 2400w",
//        ],
//        'image_partial_width_square_responsive_image_sizes' => "(min-width: 640px) 500px,100vw",
//        'image_partial_width_square_responsive_image_fallback' => "https://placem.at/places?w=640&h=640&random=partialwidthsquare 640w",
//        'image_partial_width_square_caption_text' => 'caption',
//        'image_partial_width_square_caption_credit' => 'credit',
//      ],
//    ];
//    return [
//      '#type' => 'pattern',
//      '#id' => 'image_inset',
//      '#fields' => [
//        'image_inset_responsive_image_source_set' => [
//          "https://placem.at/places?w=248&random=inset 248w",
//          "https://placem.at/places?w=556&random=inset 556w",
//          "https://placem.at/places?w=1280&random=inset 1280w",
//          "https://placem.at/places?w=1920&random=inset 1920w",
//        ],
//        'image_inset_responsive_image_sizes' => "(min-width: 950px) 448px, (min-width: 640px) 378px, 100vw",
//        'image_inset_responsive_image_fallback' => "https://placem.at/places?w=248&random=inset 248w",
//        'image_inset_caption_text' => 'caption',
//        'image_inset_caption_credit' => 'credit',
//      ],
//    ];
    return [
      '#type' => 'pattern',
      '#id' => 'image_well_width_variable_height',
      '#fields' => [
        'image_well_width_variable_height_responsive_image_source_set' => [
          "https://placem.at/places?w=640&random=wellwidthvariableheight 640w",
          "https://placem.at/places?w=1280&random=wellwidthvariableheight 1280w",
          "https://placem.at/places?w=1920&random=wellwidthvariableheight 1920w",
          "https://placem.at/places?w=2400&random=wellwidthvariableheight 2400w",
        ],
        'image_well_width_variable_height_responsive_image_sizes' => "(min-width: 1200px) 819px,(min-width: 640px) 70vw,100vw",
        'image_well_width_variable_height_responsive_image_fallback' => "https://placem.at/places?w=640&random=wellwidthvariableheight 640w",
        'image_well_width_variable_height_caption_text' => 'caption',
        'image_well_width_variable_height_caption_credit' => 'credit',
      ],
    ];
//    return [
//      '#type' => 'pattern',
//      '#id' => 'image_window_width',
//      '#fields' => [
//        'image_window_width_responsive_image_source_set' => [
//          "https://placem.at/places?w=736&h=414random=windowwidth 736w",
//          "https://placem.at/places?w=1024&h=576&random=windowwidth 1024w",
//          "https://placem.at/places?w=2048&h=1152&random=windowwidth 2048w",
//          "https://placem.at/places?w=2576&h=1449&random=windowwidth 2576w",
//        ],
//        'image_window_width_responsive_image_sizes' => "(min-width: 1200px) 819px,(min-width: 640px) 70vw,100vw",
//        'image_window_width_responsive_image_fallback' => "https://placem.at/places?w=736&h=414random=windowwidth 736w",
//        'image_window_width_caption_text' => 'caption',
//        'image_window_width_caption_credit' => 'credit',
//      ],
//    ];
//    return [
//      '#type' => 'pattern',
//      '#id' => 'image_well_width',
//      '#fields' => [
//        'image_well_width_responsive_image_source_set' => [
//          "https://placem.at/places?w=640&h=443&random=wellwidth 640w",
//          "https://placem.at/places?w=1280&h=886&random=wellwidth 1280w",
//          "https://placem.at/places?w=1920&h=1329&random=wellwidth 1920w",
//          "https://placem.at/places?w=2400&h=1662&random=wellwidth 2400w",
//        ],
//        'image_well_width_responsive_image_sizes' => "(min-width: 1200px) 819px,(min-width: 640px) 70vw,100vw",
//        'image_well_width_responsive_image_fallback' => "https://placem.at/places?w=640&h=443&random=wellwidth 640w",
//        'image_well_width_caption_text' => 'caption',
//        'image_well_width_caption_credit' => 'credit',
//      ],
//    ];
//    return [
//      '#type' => 'pattern',
//      '#id' => 'image_well_width',
//      '#fields' => [
//        'image_well_width_responsive_image_source_set' => [
//          "https://placem.at/places?w=928&h=1237&random=partialwidthportrait 928w",
//          "https://placem.at/places?w=1280&h=1707&random=partialwidthportrait 1280w",
//          "https://placem.at/places?w=1920&h=2560&random=partialwidthportrait 1920w",
//          "https://placem.at/places?w=2400&h=3200&random=partialwidthportrait 2400w",
//        ],
//        'image_well_width_responsive_image_sizes' => "(min-width: 1200px) 819px,(min-width: 640px) 70vw,100vw",
//        'image_well_width_responsive_image_fallback' => "https://placem.at/places?w=928&h=1237&random=partialwidthportrait 928w",
//        'image_well_width_caption_text' => 'caption',
//        'image_well_width_caption_credit' => 'credit',
//      ],
//    ];
//    return [
//      '#type' => 'pattern',
//      '#id' => 'image_partial_width_landscape',
//      '#fields' => [
//        'image_partial_width_landscape_responsive_image_source_set' => [
//          "https://placem.at/places?w=348&h=240&random=partialwidthlandscape 464w",
//          "https://placem.at/places?w=640&h=480&random=partialwidthlandscape 640w",
//          "https://placem.at/places?w=800&h=600&random=partialwidthlandscape 800w",
//          "https://placem.at/places?w=1200&h=960&random=partialwidthlandscape 1280w",
//          "https://placem.at/places?w=1600&h=1200&random=partialwidthlandscape 1600w",
//          "https://placem.at/places?w=1920&h=1440&random=partialwidthlandscape 1920w",
//        ],
//        'image_partial_width_landscape_responsive_image_sizes' => "(min-width: 1200px) 819px,(min-width: 640px) 70vw,100vw",
//        'image_partial_width_landscape_responsive_image_fallback' => "https://placem.at/places?w=800&h=600&random=partialwidthlandscape 800w",
//        'image_partial_width_landscape_caption_text' => 'caption',
//        'image_partial_width_landscape_caption_credit' => 'credit',
//      ],
//    ];
//    return [
//      '#type' => 'embeddable',
//      '#header_title' => 'This is cool',
//      '#header_description' => 'Description goes here',
//      '#embeddable_id' => 2,
//      '#view_mode' => 'ef_variation_1',
//      '#mode' => 'enabled',
//      '#options' => [
//        'embeddable_modifier_options' => [
//          'background_color' => 'background_color.light',
//          'alignment' => 'alignment.center',
//        ],
//      ],
//    ];
  }

  /**
   * Generates an overview table of older revisions of an embeddable.
   *
   * @param \Drupal\ef\EmbeddableInterface $embeddable
   *   An embeddable object.
   *
   * @return array
   *   An array as expected by drupal_render().
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function revisionOverview(EmbeddableInterface $embeddable) {
    $account = $this->currentUser();
    $langcode = $embeddable->language()->getId();
    $langname = $embeddable->language()->getName();
    $languages = $embeddable->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $embeddable_storage = $this->entityTypeManager()->getStorage('embeddable');
    $type = $embeddable->getType();

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $embeddable->label()]) : $this->t('Revisions for %title', ['%title' => $embeddable->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert $type embeddable revisions") || $account->hasPermission('revert all embeddable revisions') || $account->hasPermission('administer embeddable content')) && $embeddable->access('update'));
    $delete_permission = (($account->hasPermission("delete $type embeddable revisions") || $account->hasPermission('delete all embeddable revisions') || $account->hasPermission('administer embeddable content')) && $embeddable->access('delete'));

    $rows = [];
    $default_revision = $embeddable->getRevisionId();
    $current_revision_displayed = FALSE;

    foreach ($this->getRevisionIds($embeddable, $embeddable_storage) as $vid) {
      /** @var EmbeddableInterface $revision */
      $revision = $embeddable_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->revision_created->value, 'short');

        // We treat also the latest translation-affecting revision as current
        // revision, if it was the default revision, as its values for the
        // current language will be the same of the current default revision in
        // this case.
        $is_current_revision = $vid == $default_revision || (!$current_revision_displayed && $revision->wasDefaultRevision());
        if (!$is_current_revision) {
          $link = $this->getLinkGenerator()->generateFromLink(Link::fromTextAndUrl($date, $revision->toUrl('revision')));
        }
        else {
          $link = $this->getLinkGenerator()->generateFromLink($embeddable->toLink($date));
          $current_revision_displayed = TRUE;
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => ['#markup' => $revision->revision_log->value, '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        // @todo Simplify once https://www.drupal.org/node/2334319 lands.
        $this->renderer->addCacheableDependency($column['data'], $username);
        $row[] = $column;

        if ($is_current_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];

          $rows[] = [
            'data' => $row,
            'class' => ['revision-current'],
          ];
        }
        else {
          $links = [];
          //TODO
//          if ($revert_permission) {
//            $links['revert'] = [
//              'title' => $vid < $embeddable->getRevisionId() ? $this->t('Revert') : $this->t('Set as current revision'),
//              'url' => $has_translations ?
//                Url::fromRoute('node.revision_revert_translation_confirm', ['embeddable' => $embeddable->id(), 'embeddable_revision' => $vid, 'langcode' => $langcode]) :
//                Url::fromRoute('node.revision_revert_confirm', ['embeddable' => $embeddable->id(), 'embeddable_revision' => $vid]),
//            ];
//          }

//          if ($delete_permission) {
//            $links['delete'] = [
//              'title' => $this->t('Delete'),
//              'url' => Url::fromRoute('embeddable.revision_delete_confirm', ['embeddable' => $embeddable->id(), 'embeddable_revision' => $vid]),
//            ];
//          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];

          $rows[] = $row;
        }
      }
    }

    $build['embeddable_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
//      '#attached' => [
//        'library' => ['node/drupal.node.admin'],
//      ],
//      '#attributes' => ['class' => 'node-revision-table'],
    ];

    $build['pager'] = ['#type' => 'pager'];

    return $build;
  }

  /**
   * Gets a list of embeddable revision IDs for a specific embeddable.
   *
   * @param EmbeddableInterface $embeddable
   *   The embeddable entity.
   * @param EntityStorageInterface $embeddable_storage
   *   The embeddable storage handler.
   *
   * @return int[]
   *   Embeddable revision IDs (in descending order).
   */
  protected function getRevisionIds(EmbeddableInterface $embeddable, EntityStorageInterface $embeddable_storage) {
    $result = $embeddable_storage->getQuery()
      ->allRevisions()
      ->condition($embeddable->getEntityType()->getKey('id'), $embeddable->id())
      ->sort($embeddable->getEntityType()->getKey('revision'), 'DESC')
      ->pager(50)
      ->execute();
    return array_keys($result);
  }
}
