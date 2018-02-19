<?php


namespace Drupal\blog_list_page\Plugin\Block;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Block\BlockBase;
use PDO;

/**
 * Provides a 'Blog List Page' block.
 *
 * @Block(
 *   id = "blog_list_page",
 *   admin_label = @Translation("Blog List Block"),
 *   category = @Translation("Blocks")
 * )
 */
class BlogListPage extends BlockBase implements BlockPluginInterface
{
    /**
     * {@inheritdoc}
     */
    function buildContent()
    {
        $query = \Drupal::database()->select('node_field_data', 'n');
        $query->condition('n.type', 'blog', '=');

        $query->leftJoin('node__field_blog_image', 'bi', 'bi.entity_id = n.nid');

        $query->leftJoin('node__field_categories', 'fc', 'fc.entity_id = n.nid');

        $query->leftJoin('taxonomy_term_field_data', 't', 'fc.field_categories_target_id = t.tid');

        $query->leftJoin('node__field_date_blog', 'fdb', 'fdb.entity_id = n.nid');

        $query->leftJoin('node__body', 'b', 'b.entity_id = n.nid');

        $query->leftJoin('node__field_blog_author', 'ba', 'ba.entity_id = n.nid');

        $query->leftJoin('users_field_data', 'u', 'ba.field_blog_author_target_id = u.uid');

        $query->leftJoin('node__field_likes', 'fl', 'fl.entity_id = n.nid');

        $query->leftJoin('comment_entity_statistics', 'ces', 'ces.entity_id = n.nid');

        $query->addField('n', 'nid');
        $query->addField('t', 'tid');
        $query->addField('u', 'uid');
        $query->addField('n', 'title');
        $query->addField('b', 'body_value');
        $query->addField('t', 'name', 'taxonomy_name');
        $query->addField('bi', 'field_blog_image_target_id', 'image');
        $query->addField('fdb', 'field_date_blog_value');
        $query->addField('u', 'name', 'username');
        $query->addField('ces', 'comment_count');
        $query->addField('fl', 'field_likes_likes', 'likes');

        $results = $query->execute()->fetchAll(PDO::FETCH_GROUP);

        $output = [];

        foreach($results as $key => $result ) {
            $entry = [];

            foreach( $result as $node ) {
                $file = File::load($node->image);
                $url = \Drupal\image\Entity\ImageStyle::load('blog_default_img')->buildUrl($file->getFileUri());
                $alias_node = \Drupal::service('path.alias_manager')->getAliasByPath('/node/' . $key);
                $alias_taxonomy = \Drupal::service('path.alias_manager')->getAliasByPath('/' . $node->taxonomy_name);
                $alias_user = \Drupal::service('path.alias_manager')->getAliasByPath('/user/' . $node->uid);

                $alias_tax = str_replace(' ', '-', $alias_taxonomy);

                $date = date('F d, Y',strtotime($node->field_date_blog_value));

                $entry['nid'] = $alias_node;
                $entry['tid'] = $alias_tax;
                $entry['uid'] = $alias_user;
                $entry['title'] = $node->title;
                $entry['body'] = substr(strip_tags(str_replace(array("\r", "\n"), '', $node->body_value)), 0, 110);

                //@todo If current logic that node has maximum input values of 2 taxonomy terms is changed, change logic bellow.
                if (!isset($entry['taxonomy_name'][0])) {
                    $entry['taxonomy_name'][] = [
                        'name' => strip_tags($node->taxonomy_name),
                        'url' => $alias_tax
                    ];
                } else {
                    if ($entry['taxonomy_name'][0]['name'] !== $node->taxonomy_name) {
                        $entry['taxonomy_name'][1] = [
                            'name' => strip_tags($node->taxonomy_name),
                            'url' => $alias_tax
                        ];
                    }
                }
                $entry['image'] = $url;
                $entry['field_date_blog_value'] = $date;
                $entry['username'] = $node->username;

                if(empty($entry['comment_count']))
                {
                    $entry['comment_count']= $node->comment_count;
                }
                $entry['likes'] = $node->likes;
            }
            $output[] = $entry;
        }
        return $output;
    }
    /**
     * {@inheritdoc}
     */
    public function build()
    {
        return array(
            '#theme' => 'blog_list_page',
            '#content' => $this->buildContent(),
            '#cache' => [
                'max-age' => 0,
            ],
        );
    }


}