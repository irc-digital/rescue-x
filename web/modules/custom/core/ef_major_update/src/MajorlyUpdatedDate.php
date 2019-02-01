<?php

namespace Drupal\ef_major_update;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MajorlyUpdatedDate implements ContainerInjectionInterface {

  /**
   * @var TimeInterface
   */
  protected $time;

  public function __construct(TimeInterface $time) {
    $this->time = $time;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('datetime.time')
    );
  }

  /**
   * Set the majorly updated timestamp
   *
   * @param $node
   */
  public function updateDate(NodeInterface $node) {

    if($node->id() && $node->major_update) {
      // editor has marked this as a major change - set the majorly_updated field
      // with the request timestamp (this will ensure it matched the changed field)
      $node->field_majorly_updated = $this->time->getRequestTime();
    } else {
      $no_major_update = !$node->id();

      if (!$no_major_update) {
        // check whether this is a new language version
        $node_lang_code = $node->language()->getId();

        /** @var NodeInterface $original_node */
        $original_node = $node->original;

        // logic hears says
        // a) if this is a new translation
        // b) or the majorly update field has not been set
        // then no major update
        $no_major_update = $original_node->language()->getId() !== $node_lang_code || is_null($original_node->field_majorly_updated->value);
      }

      if ($no_major_update) {
        $node->field_majorly_updated = NULL;
      }
    }
  }

  /**
   * Callback to node edit form submit
   *
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public static function formSubmit (&$form, FormStateInterface $form_state) {
    $node = $form_state->getFormObject()->getEntity();
    $major_update = $form_state->getValue('major_update');

    $node->major_update = $major_update ? TRUE : FALSE;
  }
}