<?php

namespace Drupal\ef_icon_library\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derivative
 */
class ThemeIconProvider extends DeriverBase implements ContainerDeriverInterface {
  /**
   * Stores all entity row plugin information.
   *
   * @var array
   */
  protected $derivatives = [];

  /**
   * The base plugin ID that the derivative is for.
   *
   * @var string
   */
  protected $basePluginId;

  /**
   * @var ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Constructs a UnpackedAttributeField object.
   *
   * @param string $base_plugin_id
   *   The base plugin ID.
   */
  public function __construct($base_plugin_id, ThemeManagerInterface $themeManager, ThemeHandlerInterface $themeHandler) {
    $this->basePluginId = $base_plugin_id;
    $this->themeManager = $themeManager;
    $this->themeHandler = $themeHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('theme.manager'),
      $container->get('theme_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {

    $default_theme_name = $this->themeHandler->getDefault();

    /** @var \Drupal\Core\Extension\Extension $themeInfo */
    $themeInfo = $this->themeHandler->listInfo();

    /** @var \Drupal\Core\Extension\Extension $default_theme */
    $default_theme = $themeInfo[$default_theme_name];

    if (isset($default_theme->info['ef_icon_library'])) {
      $icon_library_settings = $default_theme->info['ef_icon_library'];

      if (isset($icon_library_settings["symbol_path"])) {
        $key = str_replace('.', '_', $default_theme_name);

        $this->derivatives[$key] = [
            'theme_name' => $default_theme_name,
            'symbol_path' => $icon_library_settings["symbol_path"],
            'symbol_prefix' => $icon_library_settings["symbol_prefix"] ? $icon_library_settings["symbol_prefix"] : '',
            'exclude' => $icon_library_settings["exclude"] ? $icon_library_settings["exclude"] : [],
            'id' => sprintf('%s:%s', $this->basePluginId, $key),
          ] + $base_plugin_definition;
      }
    }

    return $this->derivatives;
  }


}