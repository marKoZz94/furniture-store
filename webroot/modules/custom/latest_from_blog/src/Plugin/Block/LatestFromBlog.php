<?php


namespace Drupal\latest_from_blog\Plugin\Block;

use Drupal\file\Entity\File;
use Drupal\Core\Block\BlockBase;


/**
 * Provides a 'Latest From Blog' block.
 *
 * @Block(
 *   id = "latest_from_blog",
 *   admin_label = @Translation("Latest From Blog Block"),
 *   category = @Translation("Blocks")
 * )
 */
class LatestFromBlog extends BlockBase
{
    /**
     * {@inheritdoc}
     */
    function buildContent()
    {
        $latest = $this->buildLatest();
        $popular = $this->buildPopular();
        $comments = $this->buildComments();

        $data = [
            'latest' => $latest,
            'popular' => $popular,
            'comments' => $comments,
        ];
        return $data;

    }

    public function buildLatest()
    {
        $query = \Drupal::database()->select('node_field_data', 'n');
        $query->condition('n.type', 'blog', '=');

        $query->innerJoin('node__field_blog_image', 'bi', 'bi.entity_id = n.nid');

        $query->innerJoin('node__field_date_blog', 'fdb', 'fdb.entity_id = n.nid');

        $query->addField('n', 'nid');
        $query->addField('n', 'title');
        $query->addField('bi', 'field_blog_image_target_id', 'image');
        $query->addField('fdb', 'field_date_blog_value', 'date');

        $query->orderBy('date', 'DESC');

        $results = $query->execute()->fetchAll();

        $data = [];

        foreach ($results as $result) {
            $file = File::load($result->image);
            $url = \Drupal\image\Entity\ImageStyle::load('thumbnail')->buildUrl($file->getFileUri());
            $alias_node = \Drupal::service('path.alias_manager')->getAliasByPath('/node/' . $result->nid);

            $date = date('F d, Y', strtotime($result->date));
            $data[] = [
                'alias' => $alias_node,
                'title' => $result->title,
                'image' => $url,
                'date' => $date,
            ];
            if (count($data) > 2) {
                if (!empty($data[3])) {
                    unset($data[3]);
                }
                return $data;
            }
        }

        return $data;

    }

    public function buildPopular()
    {
        $query = \Drupal::database()->select('node_field_data', 'n');
        $query->condition('n.type', 'blog', '=');

        $query->innerJoin('node__field_blog_image', 'bi', 'bi.entity_id = n.nid');

        $query->innerJoin('node__field_date_blog', 'fdb', 'fdb.entity_id = n.nid');

        $query->innerJoin('node_counter', 'nc', 'nc.nid = n.nid');

        $query->addField('n', 'nid');
        $query->addField('n', 'title');
        $query->addField('bi', 'field_blog_image_target_id', 'image');
        $query->addField('fdb', 'field_date_blog_value', 'date');

        $query->orderBy('totalcount', 'DESC');

        $results = $query->execute()->fetchAll();

        $data = [];

        foreach ($results as $result) {
            $file = File::load($result->image);
            $url = \Drupal\image\Entity\ImageStyle::load('thumbnail')->buildUrl($file->getFileUri());
            $alias_node = \Drupal::service('path.alias_manager')->getAliasByPath('/node/' . $result->nid);

            $date = date('F d, Y', strtotime($result->date));
            $data[] = [
                'alias' => $alias_node,
                'title' => $result->title,
                'image' => $url,
                'date' => $date,
            ];
            if (count($data) > 2) {
                if (!empty($data[3])) {
                    unset($data[3]);
                }
                return $data;
            }
        }

        return $data;
    }

    public function buildComments()
    {
        $query = \Drupal::database()->select('node_field_data', 'n');
        $query->condition('n.type', 'blog', '=');

        $query->innerJoin('node__field_reply', 'lr', 'lr.entity_id = n.nid');

        $query->innerJoin('comment_entity_statistics', 'ces', 'ces.entity_id = lr.entity_id');

        $query->innerJoin('comment_field_data', 'cfd', 'cfd.entity_id = lr.entity_id');

        $query->innerJoin('comment__field_comment', 'fc', 'fc.entity_id = cfd.cid');

        $query->innerJoin('comment__field_b_name', 'bn', 'bn.entity_id = cfd.cid');

        $query->addField('n', 'nid');
        $query->addField('n', 'title');
        $query->addField('cfd', 'created');
        $query->addField('fc', 'field_comment_value', 'comment');
        $query->addField('bn', 'field_b_name_value', 'name');

        $query->orderBy('created', 'DESC');

        $results = $query->execute()->fetchAll(\PDO::FETCH_UNIQUE);

        $data = [];

        foreach ($results as $key => $result) {
            $alias_node = \Drupal::service('path.alias_manager')->getAliasByPath('/node/' . $key);
            $data [] = [
                'alias' => $alias_node,
                'title' => $result->title,
                'comment' => $result->comment,
                'name' => $result->name,
                'created' => \Drupal::service('date.formatter')->formatInterval(time() - $result->created),
            ];

            if (count($data) > 2) {
                if (!empty($data[3])) {
                    unset($data[3]);
                }
                return $data;
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {

        return array(
            '#theme' => 'latest_from_blog',
            '#content' => $this->buildContent(),
            '#cache' => [
                'max-age' => 0,
            ],
        );
    }


}