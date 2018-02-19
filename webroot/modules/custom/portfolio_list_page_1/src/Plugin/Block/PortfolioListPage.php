<?php


namespace Drupal\portfolio_list_page_1\Plugin\Block;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Block\BlockBase;


/**
 * Provides a 'Portfolio List Page' block.
 *
 * @Block(
 *   id = "portfolio_list_page_1",
 *   admin_label = @Translation("Portfolio List Page Block"),
 *   category = @Translation("Blocks")
 * )
 */
class PortfolioListPage extends BlockBase implements BlockPluginInterface{
        /**
         * {@inheritdoc}
         */
    function buildContent()
    {
        $query = \Drupal::database()->select('node_field_data', 'n');
        $query->condition('n.type', 'portfolio', '=');

        $query->innerJoin('node__field_po', 'po', 'po.entity_id = n.nid');

        $query->innerJoin('node__field_p_categories', 'pc', 'pc.entity_id = n.nid');

        $query->innerJoin('taxonomy_term_field_data', 't', 'pc.field_p_categories_target_id = t.tid');

        $query->addField('n', 'nid');
        $query->addField('t', 'tid');
        $query->addField('n', 'title');
        $query->addField('po', 'field_po_target_id', 'image');
        $query->addField('t', 'name', 'taxonomy_name');

        $data = [];

        $results = $query->execute()->fetchAll();

        foreach ( $results as $result ) {
            $file = File::load($result->image);
            $url = \Drupal\image\Entity\ImageStyle::load('portfolio_list_page_img')->buildUrl($file->getFileUri());
            $alias_node = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$result->nid);
            $alias_taxonomy = \Drupal::service('path.alias_manager')->getAliasByPath('/'.$result->taxonomy_name);

            $alias_tax = str_replace(' ', '-', $alias_taxonomy);

            $data[] = [
                'alias' => $alias_node,
                'alias2' => $alias_tax,
                'title' => $result->title,
                'taxonomy_name' =>  [
                    'name'  => strip_tags($result->taxonomy_name),
                    'url'   => $alias_tax
                ],
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
            '#theme'    => 'portfolio_list_page_1',
            '#content'  => $this->buildContent(),
            '#cache'    => [
                'max-age' => 0,
            ],
        );
    }


}