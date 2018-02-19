<?php


namespace Drupal\from_the_blog\Plugin\Block;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Block\BlockBase;


/**
 * Provides a 'From The Blog' block.
 *
 * @Block(
 *   id = "from_the_blog",
 *   admin_label = @Translation("From The Blog Block"),
 *   category = @Translation("Blocks")
 * )
 */
class FromTheBlog extends BlockBase implements BlockPluginInterface{
        /**
         * {@inheritdoc}
         */
    function buildContent()
    {
        $query = \Drupal::database()->select('node_field_data', 'n');
        $query->condition('n.type', 'blog', '=');

        $query->innerJoin('node__field_blog_image', 'bi', 'bi.entity_id = n.nid');

        $query->innerJoin('node__field_date_blog', 'fdb', 'fdb.entity_id = n.nid');

        $query->addField('n', 'nid');
        $query->addField('n', 'title');
        $query->addField('bi', 'field_blog_image_target_id', 'image');
        $query->addField('fdb', 'field_date_blog_value', 'date');

        $data = [];

        $results = $query->execute()->fetchAll();

        foreach ( $results as $result ) {
            $file = File::load($result->image);
            $url = \Drupal\image\Entity\ImageStyle::load('blog_slider_img')->buildUrl($file->getFileUri());

            $date = date('F d, Y',strtotime($result->date));

            $data[] = [
                'title' => $result->title,
                'image' => $url,
                'date' => $date,
            ];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function build () {

        return array(
            '#theme'    => 'from_the_blog',
            '#content'  => $this->buildContent(),
            '#cache'    => [
                'max-age' => 0,
            ],
        );
    }


}