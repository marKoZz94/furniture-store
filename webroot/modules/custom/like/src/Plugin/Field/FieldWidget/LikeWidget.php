<?php

namespace Drupal\like\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'like_widget' widget.
 *
 * @FieldWidget(
 *   id = "like_widget",
 *   label = @Translation("Like dislike widget"),
 *   field_types = {
 *     "like"
 *   }
 * )
 */
class LikeWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = [];

    $element['likes'] = [
      '#title' => t('Likes'),
      '#type' => 'number',
      '#default_value' => isset($items[$delta]->likes) ? $items[$delta]->likes : 0,
      '#min' => 0,
    ];
    $element['dislikes'] = [
      '#title' => t('Dislikes'),
      '#type' => 'number',
      '#default_value' => isset($items[$delta]->dislikes) ? $items[$delta]->dislikes : 0,
    ];

    return $element;
  }

}
