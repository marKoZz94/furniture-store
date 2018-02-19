<?php


namespace Drupal\hero_slider\Plugin\Block;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Block\BlockBase;


/**
 * Provides a 'Hero Slider' block.
 *
 * @Block(
 *   id = "hero_slider",
 *   admin_label = @Translation("Hero Slider Block"),
 *   category = @Translation("Blocks")
 * )
 */
class HeroSlider extends BlockBase implements BlockPluginInterface{
        /**
         * {@inheritdoc}
         */
    function buildContent()
    {
        $query = \Drupal::database()->select('node_field_data', 'n');
        $query->condition('n.type', 'hero_slider', '=');

        $query->leftJoin('node__field_hero_image', 'hi', 'hi.entity_id = n.nid');

        $query->leftJoin('node__field_text', 'ft', 'ft.entity_id = n.nid');

        $query->leftJoin('node__field_explore_store', 'es', 'es.entity_id = n.nid');

        $query->addField('n', 'nid');
        $query->addField('hi', 'field_hero_image_target_id', 'image');
        $query->addField('es', 'field_explore_store_title', 'link');
        $query->addField('ft', 'field_text_value', 'text');

        $data = [];

        $results = $query->execute()->fetchAll();

        foreach ( $results as $result ) {
            $file = File::load($result->image);
            $url = \Drupal\image\Entity\ImageStyle::load('hero_slider_img_1460x740')->buildUrl($file->getFileUri());

            $data[$result->nid]['image'] = $url;
            $data[$result->nid]['text'][] = $result->text;
            $data[$result->nid]['link'] = $result->link;

        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function build () {

        return array(
            '#theme'    => 'hero_slider',
            '#content'  => $this->buildContent(),
            '#cache'    => [
                'max-age' => 0,
            ],
        );
    }


}