<?php

namespace Drupal\like\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'like' field type.
 *
 * @FieldType(
 *   id = "like",
 *   label = @Translation("Like"),
 *   description = @Translation("Like"),
 *   default_widget = "like_widget",
 *   default_formatter = "like_formatter"
 * )
 */
class LikeField extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = array(
      'columns' => array(
        'likes' => array(
          'type' => 'varchar',
          'length' => 256,
          'not null' => FALSE,
        ),
        'dislikes' => array(
          'type' => 'varchar',
          'length' => 256,
          'not null' => FALSE,
        ),
        'clicked_by' => array(
          'type' => 'blob',
          'size' => 'big',
          'not null' => FALSE,
        ),
      ),
    );

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['likes'] = DataDefinition::create('string')
      ->setLabel(t('likes label'));
    $properties['dislikes'] = DataDefinition::create('string')
      ->setLabel(t('dislikes label'));
    $properties['clicked_by'] = DataDefinition::create('string')
      ->setLabel(t('clicked by label'));

    return $properties;
  }

}
