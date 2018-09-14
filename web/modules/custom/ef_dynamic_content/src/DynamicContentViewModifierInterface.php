<?php

namespace Drupal\ef_dynamic_content;

use Drupal\views\ViewExecutable;

/**
 * Interface DynamicContentViewModifierInterface
 * @package Drupal\ef_dynamic_content
 */
interface DynamicContentViewModifierInterface {
  /**
   * Add contextual filters to the view based on the information in the arguments
   * passed in, including the parent embeddable
   *
   * @param \Drupal\views\ViewExecutable $view
   * @return mixed
   */
  public function addContextualFilter (ViewExecutable $view);

  /**
   * Determine which type of dynamic content is on the site and then filter by it
   *
   * @param \Drupal\views\ViewExecutable $view
   * @return mixed
   */
  public function addTypeFilter (ViewExecutable $view);

  /**
   * This view only supports odd number of items. If the results back from the
   * database indicate an even number of rows then drop one off the end.
   *
   * @param \Drupal\views\ViewExecutable $view
   * @return mixed
   */
  public function ensureRenderingOddNumberOfItems (ViewExecutable $view);

  /**
   * The dynamic embeddable supports an editorially set sticky item at the top
   * of the list. If a sticky item is set then make sure the query has the
   * appropriate changes made to ensure it is at the top.
   *
   * @param \Drupal\views\ViewExecutable $view
   * @return mixed
   */
  public function ensureStickItemAtTopOfList (ViewExecutable $view);
}