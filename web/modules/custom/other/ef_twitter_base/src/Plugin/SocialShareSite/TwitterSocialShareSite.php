<?php

namespace Drupal\ef_twitter_base\Plugin\SocialShareSite;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\ef_icon_library\IconLibraryInterface;
use Drupal\ef_social_share\Annotation\SocialShareSite;
use Drupal\ef_social_share\SocialShareSiteBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The Twitter social share site plugin
 *
 * @SocialShareSite(
 *   id = "twitter_social_share_site",
 *   label = @Translation("Twitter"),
 * )
 */
class TwitterSocialShareSite extends SocialShareSiteBase implements ContainerFactoryPluginInterface {

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
    return [
      'twitter_send_via' => 'via @theIRC',
      'twitter_share_prefix' => '[node:custom-social-share-title]',
    ] + parent::defaultConfiguration();
  }

  /**
   * @inheritdoc
   */
  public function getLink(array $context = []) {
    $prefix = $this->configuration['twitter_share_prefix'];
    $via = $this->configuration['twitter_send_via'];

    $text = $prefix . ' ' . $via;

    $token_service = \Drupal::token();

    $text = $token_service->replace($text, $context);

    return 'https://twitter.com/intent/tweet?text=' . UrlHelper::encodePath($text);
  }


  /**
   * @inheritdoc
   */
  public function getLibraries(array $context = []) {
    return ['ef_twitter_base/twitter_widgets_js'] + parent::getLibraries($context);
  }

  /**
   * @inheritdoc
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['twitter_share_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tweet prefixed text'),
      '#description' => $this->t('This text will appear before the URL and via.'),
      '#default_value' => $this->configuration['twitter_share_prefix'],
    ];

    $form['twitter_send_via'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Send via'),
      '#description' => $this->t('When sending a tweet this is the screen name of the user to attribute the Tweet to. This comes after the URL in the share.'),
      '#default_value' => $this->configuration['twitter_send_via'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['twitter_send_via'] = $form_state->getValue('twitter_send_via');
    $this->configuration['twitter_share_prefix'] = $form_state->getValue('twitter_share_prefix');
    parent::submitConfigurationForm($form, $form_state);
  }

}
