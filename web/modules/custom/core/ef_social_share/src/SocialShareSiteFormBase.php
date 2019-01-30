<?php

namespace Drupal\ef_social_share;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base form for social share site forms.
 */
abstract class SocialShareSiteFormBase extends EntityForm {

  /**
   * The social share site storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * The social share site entity.
   *
   * @var \Drupal\ef_social_share\SocialShareSiteConfigEntityInterface
   */
  protected $entity;

  /**
   * Constructs a new social share site form.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The social share site storage.
   */
  public function __construct(EntityStorageInterface $storage) {
    $this->storage = $storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('social_share_site')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $this->entity->label(),
      '#maxlength' => '255',
      '#description' => $this->t('A unique label for this social share site.'),
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#disabled' => !$this->entity->isNew(),
      '#maxlength' => 64,
      '#description' => $this->t('A unique name for this social share site. It must only contain lowercase letters, numbers and underscores.'),
      '#machine_name' => [
        'exists' => [$this, 'exists'],
      ],
    ];
    $form['plugin'] = [
      '#type' => 'value',
      '#value' => $this->entity->get('plugin'),
    ];

    if ($plugin = $this->getPlugin()) {
      $form += $plugin->buildConfigurationForm($form, $form_state);
    }

    return parent::form($form, $form_state);
  }

  /**
   * Determines if the social share site already exists.
   *
   * @param string $id
   *   The social share site ID.
   *
   * @return bool
   *   TRUE if the social share site exists, FALSE otherwise.
   */
  public function exists($id) {
    $action = $this->storage->load($id);
    return !empty($action);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    unset($actions['delete']);
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    if ($plugin = $this->getPlugin()) {
      $plugin->validateConfigurationForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    if ($plugin = $this->getPlugin()) {
      $plugin->submitConfigurationForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->entity->save();
    $this->messenger()->addStatus($this->t('The social share site has been successfully saved.'));

    $form_state->setRedirect('entity.social_share_sites.collection');
  }

  /**
   * Gets the social share site plugin while ensuring it implements configuration form.
   *
   * @return \Drupal\ef_social_share\SocialShareSiteInterface|\Drupal\Core\Plugin\PluginFormInterface|null
   *   The social share site plugin, or NULL if it does not implement configuration forms.
   */
  protected function getPlugin() {
    if ($this->entity->getPlugin() instanceof PluginFormInterface) {
      return $this->entity->getPlugin();
    }
    return NULL;
  }

}
