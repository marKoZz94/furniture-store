<?php


namespace Drupal\breadcrumb_member_profile\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Block\BlockPluginInterface;


/**
 * Provides a 'Breadcrumb Member Profile' block.
 *
 * @Block(
 *   id = "breadcrumb_member_profile",
 *   admin_label = @Translation("Breadcrumb Member Profile Block"),
 *   category = @Translation("Blocks")
 * )
 */
class BreadcrumbMemberProfile extends BlockBase implements BlockPluginInterface
{
    /**
     * {@inheritdoc}
     */
    function buildContent()
    {
        $current_path = \Drupal::service('path.current')->getPath();

        $node_id = explode("/", $current_path);
        $id = $node_id[2];

        $node = Node::load($id);

        $member = $node->get('title')->getValue();
        $taxonomy = $node->get('field_profile_categories')->getValue();

        $tax = Term::load($taxonomy[0]['target_id']);
        $tax_name = $tax->get('name')->getValue();

        $data = [
            'title' => $member[0]['value'],
            'name' => $tax_name[0]['value'],
        ];

        return $data;

    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        return array(
            '#theme' => 'breadcrumb_member_profile',
            '#content' => $this->buildContent(),
            '#cache' => [
                'max-age' => 0,
            ],
        );
    }

}