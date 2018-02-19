<?php


namespace Drupal\prev_next_portfolio\Plugin\Block;

use Drupal\node\Entity\Node;
use Drupal\Core\Block\BlockBase;


/**
 * Provides a 'Next Previous Portfolio' block.
 *
 * @Block(
 *   id = "prev_next_portfolio",
 *   admin_label = @Translation("Next Previous Portfolio Block"),
 *   category = @Translation("Blocks")
 * )
 */
class PrevNext extends BlockBase {
        /**
         * {@inheritdoc}
         */
    function build()
    {
        $current_node = \Drupal::routeMatch()->getParameter('node')->Id();
        # Next
        $query = \Drupal::database()->select('node_field_data', 'n');
        $query->addField('n', 'nid');
        $query->addField('n', 'title');
        $query->condition('n.nid', $current_node, '>');
        $query->condition('n.type', 'portfolio', '=');
        $query->range(0, 1);
        $previous = $query->execute()->fetchAssoc();

        # Previous
        $query = \Drupal::database()->select('node_field_data', 'n');
        $query->addField('n', 'nid');
        $query->addField('n', 'title');
        $query->condition('n.nid', $current_node, '<');
        $query->condition('n.type', 'portfolio', '=');
        $query->orderBy('nid', 'DESC');
        $query->range(0, 1);
        $next = $query->execute()->fetchAssoc();


        # Markup
        $markup = '<div>';
        if( isset($previous) && is_numeric($previous['nid'])) {
//            $node = Node::load($previous);
            $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$previous['nid']);
            $markup .= '<a class="prev" href="'.$alias.'">previous <span class="title">' . $previous['title'] . '</span></a> ';
        }
        if( isset($next)&& is_numeric($next['nid'])) {
//            $node = Node::load($next);
            $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$next['nid']);
            $markup .= '<a class="next" href="'.$alias.'">next <span class="title">' . $next['title'] . '</span></a>';
        }
        $markup .= '</div>';

        # Cache
        return [
            '#markup' =>$markup,
            '#cache'   => ['max-age' => 0],

        ];

    }

}