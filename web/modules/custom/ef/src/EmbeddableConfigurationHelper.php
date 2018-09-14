<?php


namespace Drupal\ef;


use Drupal\field\Entity\FieldStorageConfig;

class EmbeddableConfigurationHelper {

  /**
   * Add the supplied embeddable bundle name to the embeddables field. This
   * is for administrative convenience
   *
   * @param $embeddable_type
   * @return \Drupal\ef\EmbeddableConfigurationHelper
   */
  public function addToEmbeddableField ($embeddable_type) {
    return $this->addToField($embeddable_type, 'field_embeddables');
  }

  /**
   * Add the supplied embeddable bundle name to the hero embeddable field. This
   * is for administrative convenience
   *
   * @param $embeddable_type
   * @return \Drupal\ef\EmbeddableConfigurationHelper
   */
  public function addToHeroEmbeddableField ($embeddable_type) {
    return $this->addToField($embeddable_type, 'field_hero_embeddable');
  }

  /**
   * Ensures that supplied content type is made visible to the hero block. This
   * is useful if the content type has its own hero view mode
   *
   * @param $content_type
   */
  public function addContentTypeToHeroBlock ($content_type) {
    $hero_block_visibility = \Drupal::configFactory()->getEditable('block.block.hero')->get('visibility');
    $hero_block_visibility['entity_bundle:node']['bundles'][$content_type] = $content_type;
    \Drupal::configFactory()->getEditable('block.block.hero')->set('visibility', $hero_block_visibility)->save(TRUE);
  }

  public function addCropToMediaImageForm ($crop) {
    /** @var \Drupal\Core\Config\Config $editableConfig */
    $editableConfig = \Drupal::configFactory()->getEditable('core.entity_form_display.media.ef_image.default');
    $media_image_form_content = $editableConfig->get('content');
    $media_image_form_content['field_ef_image']['settings']['crop_list'][] = $crop;
    $editableConfig->set('content', $media_image_form_content)->save(TRUE);
  }

  public function removeCropFromMediaImageForm ($crop) {
    /** @var \Drupal\Core\Config\Config $editableConfig */
    $editableConfig = \Drupal::configFactory()->getEditable('core.entity_form_display.media.ef_image.default');
    $media_image_form_content = $editableConfig->get('content');

    if (($key = array_search($crop, $media_image_form_content['field_ef_image']['settings']['crop_list'])) !== false) {
      unset($media_image_form_content['field_ef_image']['settings']['crop_list'][$key]);
      $editableConfig->set('content', $media_image_form_content)->save(TRUE);
    }
  }

  public function addTextFormatFilter($filter, $filter_id, $filter_details, $place_after = NULL) {
    $filter_editable = \Drupal::configFactory()->getEditable(sprintf('filter.format.%s', $filter));
    $filters = $filter_editable->get('filters');

    if (count($filters) > 0) {
      $weight = 0;

      if ($place_after && isset($filters[$place_after]['weight'])) {
        $weight = $filters[$place_after]['weight'] + 0.1;
      }

      $filter_details = $filter_details + [
          'id' => $filter_id,
          'weight' => $weight,
          'status' => TRUE,
        ];
      $filters += [$filter_id => $filter_details];

      $filter_editable->set('filters', $filters)->save(TRUE);
    }
  }

  public function removeTextFormatFilter($filter, $filter_id) {
    $filter_editable = \Drupal::configFactory()->getEditable(sprintf('filter.format.%s', $filter));
    $filters = $filter_editable->get('filters');
    if (isset($filters[$filter_id])) {
      unset ($filters[$filter_id]);
      $filter_editable->set('filters', $filters)->save(TRUE);
    }
  }

  protected function addToField ($embeddable_type, $field_name) {
    /** @var \Drupal\field\FieldStorageConfigInterface $field_config */
    $field_config = FieldStorageConfig::loadByName('node', $field_name);

    if (!is_null($field_config)) {
      $bundles = $field_config->getBundles();

      foreach ($bundles as $bundle) {
        $config_name = sprintf ('field.field.node.%s.%s', $bundle, $field_name);

        $hero_embeddable_setting = \Drupal::configFactory()->getEditable($config_name)->get('settings');
        $hero_embeddable_setting['handler_settings']['target_bundles'][$embeddable_type] = $embeddable_type;
        \Drupal::configFactory()->getEditable($config_name)->set('settings', $hero_embeddable_setting)->save(TRUE);
      }

    }

    return $this;
  }
}
