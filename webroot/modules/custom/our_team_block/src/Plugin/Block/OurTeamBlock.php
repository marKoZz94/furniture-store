<?php


namespace Drupal\our_team_block\Plugin\Block;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\file\Entity\File;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Block\BlockBase;


/**
 * Provides a 'Our Team' block.
 *
 * @Block(
 *   id = "our_team_block",
 *   admin_label = @Translation("Our Team Block"),
 *   category = @Translation("Blocks")
 * )
 */
class OurTeamBlock extends BlockBase implements BlockPluginInterface{
        /**
         * {@inheritdoc}
         */
    function buildContent()
    {
        $query = \Drupal::database()->select('node_field_data', 'n');
        $query->condition('n.type', 'our_team', '=');

        $query->innerJoin('node__field_profile_image', 'pi', 'pi.entity_id = n.nid');

        $query->innerJoin('node__field_profile_categories', 'fpc', 'fpc.entity_id = n.nid' );

        $query->innerJoin('taxonomy_term_field_data', 't', 'fpc.field_profile_categories_target_id = t.tid' );

        $query->addField('n', 'nid');
        $query->addField('n', 'title');

        $query->addField('pi', 'field_profile_image_target_id', 'image');

        $query->addField('t', 'name');

        $query->range(0, 3);

        $data = [];

        $results = $query->execute()->fetchAll();

        foreach ( $results as $result ) {
            $file = File::load($result->image);
            $url = \Drupal\image\Entity\ImageStyle::load('our_team')->buildUrl($file->getFileUri());
            $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'. $result->nid);
            $data[] = [
                'alias' => $alias,
                'title' => $result->title,
                'name' => $result->name,
                'image' => $url,
            ];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function build () {

        return array(
            '#theme'    => 'our_team_block',
            '#content'  => $this->buildContent(),
            '#cache'    => [
                'max-age' => 0,
            ],
        );
    }


}