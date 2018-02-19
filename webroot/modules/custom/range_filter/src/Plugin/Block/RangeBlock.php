<?php

namespace Drupal\range_filter\Plugin\Block;

use Drupal\Core\Block\BlockBase;


/**
 * Provides a custom block
 *
 * @Block(
 *   id = "range_block",
 *   admin_label = @Translation("Range block"),
 *   category = @Translation("Custom"),
 * )
 */
class RangeBlock extends BlockBase
{
    public function build()
    {
        $form = \Drupal::formBuilder()->getForm('Drupal\range_filter\Form\RangeForm');
        $form ['#theme'] = 'range_filter';
        $form ['#cache'] = ['max-age' => 0];
        return $form;
    }
}