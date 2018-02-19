<?php


namespace Drupal\top_rated_products\Plugin\Block;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Block\BlockBase;
use Drupal\comment\Entity\Comment;
use Drupal\node\Entity\Node;

/**
 * Provides a 'Top Rated Products' block.
 *
 * @Block(
 *   id = "top_rated_products",
 *   admin_label = @Translation("Top Rated Products Block"),
 *   category = @Translation("Blocks")
 * )
 */
class TopRatedProducts extends BlockBase implements BlockPluginInterface{
        /**
         * {@inheritdoc}
         */
    function buildContent()
    {
        $query = \Drupal::database()->select('node_field_data', 'n');
        $query->condition('n.type', 'furniture', '=');

        $query->innerJoin('node__field_price', 'fp', 'fp.entity_id = n.nid' );

        $query->innerJoin('node__field_fur_image', 'fi', 'fi.entity_id = n.nid');

        $query->condition('fi.delta', 0, '=');

        $query->innerJoin('node__field_reviews', 'fr', 'fr.entity_id = n.nid');

        $query->innerJoin('comment_entity_statistics', 'ces', 'fr.entity_id = ces.entity_id');

        $query->innerJoin('comment_field_data', 'cfd', 'ces.entity_id = cfd.entity_id');

        $query->innerJoin('comment__field_your_rating', 'cff', 'cfd.cid = cff.entity_id');

        $query->addField('n', 'nid');
        $query->addField('n', 'title');
        $query->addField('fi', 'field_fur_image_target_id', 'image');
        $query->addField('fp', 'field_price_value');
        $query->addField('cff', 'field_your_rating_rating', 'rating');

        $query->orderBy('field_your_rating_rating', 'DESC');

        $data = [];

        $ratings = $this->buildStars();

        $results = $query->execute()->fetchAll();

        foreach ( $results as $result ) {
            $file = File::load($result->image);
            $url = \Drupal\image\Entity\ImageStyle::load('sidebar_img')->buildUrl($file->getFileUri());
            $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$result->nid);

            $data[] = [
                'alias' => $alias,
                'title' => $result->title,
                'image' => $url,
                'field_price_value' => $result->field_price_value,
                'star' => $ratings,
            ];

            if (count($data) > 2){
                if( !empty($data[3])) {
                    unset($data[3]);
                }
                return $data;
            }
        }
        return $data;
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
        $query->addField('cff', 'field_your_rating_rating', 'rating');

        $comments = $query->execute()->fetchAll(\PDO::FETCH_GROUP);

        $rating = [];

        foreach ($comments as $comment) {
            $rating = floatval(array_sum($comment->rating) / count($comment->rating));
            $ratings = round(($rating / 20), 0);
            $rating = [];

//            if (isset($rate[0]['rating'])) {
//                $ratings[] = $rate[0]['rating'];
//            }

            for ($i = 0; $i < 5; $i++) {
                if ($i < $ratings) {
                    $rating[] = 'full';
                } else {
                    $rating[] = 'empty';
                }
            }


            return $rating;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function build () {

        return array(
            '#theme'    => 'top_rated_products',
            '#content'  => $this->buildContent(),
            '#cache'    => [
                'max-age' => 0,
            ],
        );
    }


}