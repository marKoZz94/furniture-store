<?php


namespace Drupal\recently_viewed_products\Plugin\Block;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides a 'Recently Viewed Products' block.
 *
 * @Block(
 *   id = "recently_viewed_products",
 *   admin_label = @Translation("Recently Viewed Products Block"),
 *   category = @Translation("Blocks")
 * )
 */
class RecentlyViewedProducts extends BlockBase implements BlockPluginInterface{
        /**
         * {@inheritdoc}
         */
    function buildContent()
    {

        $raw_nodes = $_SESSION['recently_viewed'];

        # Sorting by newest
        arsort($raw_nodes);

        # Lasting 3 Hours
        $ctr_timestamp = strtotime('3 hours ago');

        foreach ($raw_nodes as $node_id => $raw_node){
            if($raw_node < $ctr_timestamp){
                unset($raw_nodes[$node_id]);
            }
        }

        $node_ids = array_keys($raw_nodes);

        # Loading Multiple Node Ids
        $nodes = Node::loadMultiple($node_ids);

        $results = [];
        $ratings = $this->buildStars();


        foreach ( $nodes as $node ) {

            $title = $node->getTitle();
            $image = $node->get('field_fur_image')->getValue();
            $file = File::load($image[0]['target_id']);
            $image_url = \Drupal\image\Entity\ImageStyle::load('thumbnail')->buildUrl($file->getFileUri());
            $price = $node->get('field_price')->getValue()[0]['value'];

            $alias_node = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$node->id());
            $results[] = [
                'alias' => $alias_node,
                'title' => $title,
                'price' => $price,
                'image_url' => $image_url,
                'star' => $ratings,
            ];
        }

        return $results;
    }

    public function buildStars()
    {
        $query = \Drupal::database()->select('node_field_data', 'n');
        $query->condition('n.type', 'furniture', '=');

        $query->innerJoin('node__field_reviews', 'fr', 'fr.entity_id = n.nid');

        $query->innerJoin('comment_entity_statistics', 'ces', 'fr.entity_id = ces.entity_id');

        $query->innerJoin('comment_field_data', 'cfd', 'ces.entity_id = cfd.entity_id');

        $query->innerJoin('comment__field_your_rating', 'cff', 'cfd.cid = cff.entity_id');

        $query->addField('n', 'nid', 'id');
//        $query->addField('fr', 'entity_id');
        $query->addField('cff', 'field_your_rating_rating', 'rating');

        $comments = $query->execute()->fetchAll();


        $rating = [];

        $rating = floatval(array_sum($comments) / count($comments));
        $ratings = round(($rating/20),0);
        $rating = [];
        if (isset($rate[0]['rating'])) {
            $ratings[] = $rate[0]['rating'];
        }

        for($i=0;$i<5;$i++){
            if($i<$ratings){
                $rating[] = 'full';
            } else {
                $rating[] = 'empty';
            }
        }


        return $rating;
    }

    /**
     * {@inheritdoc}
     */
    public function build () {

        return array(
            '#theme'    => 'recently_viewed_products',
            '#content'  => $this->buildContent(),
            '#cache'    => [
                'max-age' => 0,
            ],
        );
    }


}

