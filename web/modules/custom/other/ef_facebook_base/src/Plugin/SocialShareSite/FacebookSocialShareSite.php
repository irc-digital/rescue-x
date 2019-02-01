<?php

namespace Drupal\ef_facebook_base\Plugin\SocialShareSite;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\ef_icon_library\IconLibraryInterface;
use Drupal\ef_social_share\Annotation\SocialShareSite;
use Drupal\ef_social_share\SocialShareSiteBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The Facebook social share site plugin
 *
 * @SocialShareSite(
 *   id = "facebook_social_share_site",
 *   label = @Translation("Facebook"),
 * )
 */
class FacebookSocialShareSite extends SocialShareSiteBase implements ContainerFactoryPluginInterface {

  public function __construct(array $configuration, $plugin_id, $plugin_definition, IconLibraryInterface $icon_library) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $icon_library);
  }
    /**
   * @inheritdoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('ef.icon_library')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [] + parent::defaultConfiguration();
  }

  /**
   * @inheritdoc
   */
  public function getLink(array $context = []) {
    return '#';
  }

  /**
   * @inheritdoc
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
  }

}
