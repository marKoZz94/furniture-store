<?php


namespace Drupal\our_collections\Plugin\Block;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Block\BlockBase;


/**
 * Provides a 'Our Collections' block.
 *
 * @Block(
 *   id = "our_collections",
 *   admin_label = @Translation("Our Collections Block"),
 *   category = @Translation("Blocks")
 * )
 */
class OurCollections extends BlockBase implements BlockPluginInterface{
        /**
         * {@inheritdoc}
         */
    function buildContent()
    {
        $query = \Drupal::database()->select('node_field_data', 'n');
        $query->condition('n.type', 'furniture', '=');

        $query->innerJoin('node__body', 'b', '.b.entity_id = n.nid');

        $query->innerJoin('node__field_fur_image', 'fi', 'fi.entity_id = n.nid');

        $query->innerJoin('node__field_categories', 'fc', 'fc.entity_id = n.nid' );

        $query->innerJoin('taxonomy_term_field_data', 't', 'fc.field_categories_target_id = t.tid' );

        $query->innerJoin('node__field_price', 'fp', 'fp.entity_id = n.nid' );

        $query->addField('n', 'nid');
        $query->addField('n', 'nid','id');
        $query->addField('t', 'tid');
        $query->addField('n', 'title');
        $query->addField('fi', 'field_fur_image_target_id', 'image');
        $query->addField('t', 'name', 'taxonomy_name');
        $query->addField('fp', 'field_price_value');
        $query->addField('b', 'body_value', 'body');

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

                if (!isset($entry['image'][0])) {
                    $entry['image'][] = [
                        'img' => $url,
                        'nid' => $alias_node,
                    ];
                } else {
                    if ($entry['image'][0]['img'] !== $url) {
                        $entry['image'][1] = [
                            'img' => $url,
                            'nid' => $alias_node,
                        ];
                    }
                }
            }
            $data[] = $entry;

            if (count($data) > 6){
                if( !empty($data[6])) {
                    unset($data[6]);
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
            '#theme'    => 'our_collections',
            '#content'  => $this->buildContent(),
            '#cache'    => [
                'max-age' => 0,
            ],
        );
    }


}