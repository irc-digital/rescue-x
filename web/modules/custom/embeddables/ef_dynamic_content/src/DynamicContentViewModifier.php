<?php

namespace Drupal\ef_dynamic_content;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\node\NodeInterface;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;

/**
 * Class DynamicContentViewModifier
 * @package Drupal\ef_dynamic_content
 */
class DynamicContentViewModifier implements DynamicContentViewModifierInterface {
  /** @var \Drupal\Core\Extension\ModuleHandlerInterface */
  protected $moduleHandler;

  public function __construct(ModuleHandlerInterface $moduleHandler) {
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * @inheritdoc
   */
  public function addContextualFilter (ViewExecutable $view) {
    // first arg is the filtering topic/country etc
    if (isset($view->args[0]) && $view->args[0] instanceof NodeInterface) {
      /** @var NodeInterface $filtering_type */
      $filtering_type = $view->args[0];
      $bundle = $filtering_type->bundle();

      $field = NULL;

      $hook_name = sprintf('dynamic_content_view_argument_field_for_%s', $bundle);
      $modules = $this->moduleHandler->getImplementations($hook_name);

      if (count($modules) > 0) {
        $module = array_pop($modules);

        $field_to_filter_on = $this->moduleHandler->invoke($module, $hook_name);

        $arguments_plugin_manager = Views::pluginManager('argument');

        /** @var \Drupal\views\Plugin\views\argument\NumericArgument $numeric_filter */
        $numeric_filter = $arguments_plugin_manager->createInstance('numeric');

        $options = [
          'table' => 'node__' . $field_to_filter_on,
          'field' => $field_to_filter_on . '_target_id',
        ];

        $numeric_filter->init($view, $view->display_handler, $options);
        $view->argument[$numeric_filter->field] = $numeric_filter;
        $view->args[0] = $filtering_type->id();
      }
    }

    // second arg is count
    if (isset($view->args[1])) {
      $count = $view->args[1];
      $view->setItemsPerPage($count);
    }

    return $this;

  }

  /**
   * @inheritdoc
   */
  public function addTypeFilter (ViewExecutable $view) {
    /** @var \Drupal\views\Plugin\ViewsPluginManager $filter_plugin_manager */
    $filter_plugin_manager = Views::pluginManager('filter');

    /** @var \Drupal\views\Plugin\views\filter\Bundle $bundle_filter */
    $bundle_filter = $filter_plugin_manager->createInstance('bundle');

    $options = [
      'table' => 'node_field_data',
      'field' => 'type',
    ];

    $bundle_filter->init($view, $view->display_handler, $options);

    $permitted_bundles = $this->moduleHandler->invokeAll('dynamic_content_view_filter_bundle');

    if (count($permitted_bundles) > 0) {
      $permitted_bundles = array_combine($permitted_bundles, $permitted_bundles);
    } else {
      $permitted_bundles = ['ignore' => 'ignore'];
    }

    $bundle_filter->value = $permitted_bundles;
    $view->filter['type'] = $bundle_filter;

    return $this;
  }

  /**
   * @inheritdoc
   */
  public function ensureRenderingOddNumberOfItems (ViewExecutable $view) {
    $items_per_page = $view->getItemsPerPage();
    $row_count = count($view->result);

    if ($row_count < $items_per_page) {
      if ($row_count % 2 === 0) {
        array_splice($view->result, $row_count - 1);
      }
    }

    return $this;
  }

  /**
   * @inheritdoc
   */
  public function ensureStickItemAtTopOfList (ViewExecutable $view) {
    /** @var \Drupal\Core\Database\Query\SelectInterface $query */
    $query = $view->build_info["query"];

    if (isset($view->args[2])) { // arg 2 is the stick node id
      $order_by = &$query->getOrderBy();
      $original_order_by = $order_by;

      foreach ($order_by as $key => $value) {
        unset($order_by[$key]);
      }
      $sticky_item_node_id = $view->args[2];
      $query->addExpression(sprintf("FIELD(nid,%s)", $sticky_item_node_id), 'sticky_order_field');
      $query->orderBy('sticky_order_field', 'DESC');

      foreach ($original_order_by as $key => $value) {
        $query->orderBy($key, $value);
      }
    }

    return $this;
  }
}