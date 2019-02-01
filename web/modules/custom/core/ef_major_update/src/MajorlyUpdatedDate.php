<?php

namespace Drupal\ef_major_update;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
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
    if($node->major_update) {
      // editor has marked this as a major change - set the majorly_updated field
      // with the request timestamp (this will ensure it matched the changed field)
      $node->field_majorly_updated = $this->time->getRequestTime();
    } else if (!$node->id()) {
      // first version - because of defaulting in the timestamp area we actually
      // want to set this to null. This makes life easier when we render
      $node->field_majorly_updated = NULL;
    }
  }
}