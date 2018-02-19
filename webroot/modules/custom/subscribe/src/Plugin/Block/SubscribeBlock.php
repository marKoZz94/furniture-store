<?php
/**
 * Created by PhpStorm.
 * User: veus
 * Date: 11/24/17
 * Time: 9:53 AM
 */
/**
 * @file
 * Contains \Drupal\subscribe\Plugin\Block\SubscribeBlock.
 */
namespace Drupal\subscribe\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;



/**
 * Provides a custom block
 *
 * @Block(
 *   id = "subscribe_block",
 *   admin_label = @Translation("Subscribe Block"),
 *   category = @Translation("Subscribe"),
 * )
 */
class SubscribeBlock extends BlockBase
{
    public function build()
    {
        // TODO: Implement build() method.
        $form = \Drupal::formBuilder()->getForm('Drupal\subscribe\Form\SubscribeForm');
        return $form;
    }
}