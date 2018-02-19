<?php


namespace Drupal\favourite_products\Plugin\Block;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Block\BlockBase;


/**
 * Provides a 'Favourite Products' block.
 *
 * @Block(
 *   id = "favourite_products",
 *   admin_label = @Translation("Favourite Products Block"),
 *   category = @Translation("Blocks")
 * )
 */
class FavouriteProducts extends BlockBase implements BlockPluginInterface{
        /**
         * {@inheritdoc}
         */
    function buildContent()
    {
        $query = \Drupal::database()->select('node_field_data', 'n');
        $query->condition('n.type', 'furniture', '=');

        $query->innerJoin('node__body', 'b', 'b.entity_id = n.nid');

        $query->innerJoin('node__field_fur_image', 'fi', 'fi.entity_id = n.nid');

        $query->condition('fi.delta', 0, '=');

        $query->innerJoin('node__field_price', 'fp', 'fp.entity_id = n.nid' );

        $query->innerJoin('node__field_categories', 'fc', 'fc.entity_id = n.nid' );

        $query->innerJoin('taxonomy_term_field_data', 't', 'fc.field_categories_target_id = t.tid' );

        $query->innerJoin('node_counter', 'nc', 'nc.nid = n.nid');

        $query->orderBy( 'totalcount', 'DESC' );

        $query->addField('n', 'nid');
        $query->addField('n', 'nid', 'id');
        $query->addField('n', 'title');
        $query->addField('fi', 'field_fur_image_target_id', 'image');
        $query->addField('fp', 'field_price_value');
        $query->addField('b', 'body_value', 'body');
        $query->addField('t', 'name', 'taxonomy_name');
        $query->addField('t', 'tid');

        $data = [];

        $results = $query->execute()->fetchAll(\PDO::FETCH_GROUP);

        foreach($results as $key => $result ) {
            $entry = [];
            foreach( $result as $node ) {
                $file = File::load($node->image);
                $url = \Drupal\image\Entity\ImageStyle::load('furniture_default_img')->buildUrl($file->getFileUri());
                $alias_node = \Drupal::service('path.alias_manager')->getAliasByPath('/node/' . $key);
                $alias_taxonomy = \Drupal::service('path.alias_manager')->getAliasByPath('/' . $node->tid);

                $alias_tax = str_replace(' ', '-', $alias_taxonomy);

                $entry['id'] = $node->id;
                $entry['nid'] = $alias_node;
                $entry['tid'] = $alias_tax;
                $entry['title'] = $node->title;
                $entry['field_price_value'] = $node->field_price_value;
                $entry['body'] = substr(strip_tags(str_replace(array("\r", "\n"), '', $node->body)), 0, 400);
                $entry['image'] = $url;

                //@todo If current logic that node has maximum input values of 2 taxonomy terms is changed, change logic bellow.
                if (!isset($entry['taxonomy_name'][0])) {
                    $entry['taxonomy_name'][] = [
                        'name' => strip_tags($node->taxonomy_name),
                        'url' => 'furniture/?taxonomy=' . $node->tid,
                    ];
                } else {
                    if ($entry['taxonomy_name'][0]['name'] !== $node->taxonomy_name) {
                        $entry['taxonomy_name'][1] = [
                            'name' => strip_tags($node->taxonomy_name),
                            'url' => 'furniture/?taxonomy=' . $node->tid,
                        ];
                    }
                }
            }
            $data[] = $entry;

            if (count($data) > 8){
                if( !empty($data[7])) {
                    unset($data[7]);
                }
                return $data;
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function build () {

        return array(
            '#theme'    => 'favourite_products',
            '#content'  => $this->buildContent(),
            '#cache'    => [
                'max-age' => 0,
            ],
        );
    }


}