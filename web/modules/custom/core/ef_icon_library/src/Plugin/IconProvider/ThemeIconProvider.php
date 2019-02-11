<?php

namespace Drupal\ef_icon_library\Plugin\IconProvider;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\ef_icon_library\IconProviderInterface;
use Drupal\ef_icon_library\Plugin\Annotation\IconProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ThemeIconProvider
 * @package Drupal\ef_test\Plugin\EmbeddableUsage
 *
 * @IconProvider(
 *    id = "theme_icon_provider",
 *    deriver = "Drupal\ef_icon_library\Plugin\Derivative\ThemeIconProvider"
 * )
 */
class ThemeIconProvider extends PluginBase implements IconProviderInterface, ContainerFactoryPluginInterface {

  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  public function getIcons () {
    $config = $this->getPluginDefinition();

    $theme_name = $config['theme_name'];
    $symbol_path = $config['symbol_path'];
    $symbol_prefix = $config['symbol_prefix'];
    $exclude = array_flip($config['exclude']);
    $theme_base_path = drupal_get_path ('theme', $theme_name);
    $symbol_folder = $theme_base_path . DIRECTORY_SEPARATOR . $symbol_path;
    $mask = '/^' . $symbol_prefix . '.*\.svg$/';

    $icon_list = file_scan_directory ($symbol_folder, $mask);

    $icons = [];
    foreach ($icon_list as $icon_key => $icon_file) {
      if (isset($exclude[$icon_file->filename]) || isset($exclude[str_replace($symbol_prefix, '', $icon_file->filename)])) {
        continue;
      }
      $icon_file->display_name = str_replace($symbol_prefix, '', $icon_file->name);
      $icon_file->id = $icon_file->display_name;
      $icons[$icon_file->id] = $icon_file;
    }

    return $icons;

  }
}