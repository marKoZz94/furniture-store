<?php

namespace Drupal\search_page\Form;

use Drupal\Core\Database\Database;
use Drupal\file\Entity\File;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use PDO;

/**
 * Class SearchForm.
 */
class SearchForm extends FormBase {


	/**
	 * {@inheritdoc}
	 */
	public function getFormId () {
		return 'search_form';
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm (array $form, FormStateInterface $form_state) {

		$form = [];

		$form['#method'] = 'GET';

		$form['filters'] = [
			'#type'        => 'fieldset',
			'#title'       => t('filters'),
			'#collapsible' => true,
			'#attributes'  => array('class' => array('inline')),
		];

		$options = [
			0 => 'Default sorting',
			1 => 'Sort by popularity',
			2 => 'Sort by average rating',
			3 => 'Sort by newness',
			4 => 'Sort by price low to high',
			5 => 'Sort by price high to low',
		];

		$form['filters']['sort'] = [
			'#type'          => 'select',
			'#options'       => $options,
			'#default_value' => isset($_GET['sort']) ? $_GET['sort'] : '',
		];

		$form['filters']['actions']['#type'] = 'actions';
		$form['filters']['actions']['submit'] = [
			'#type'  => 'submit',
			'#value' => $this->t('Submit'),
		];

		$data = $this->buildContent();

		$form['content']['data'] = $data;

		$form['#theme'] = 'search_page';

		$form['pager'] = array(
			'#type' => 'pager',
		);

		$form ['filters']['min_price'] = array(
			'#type' => 'number',
			'#placeholder' => t('Min'),
			'#min' => 0,
			'#max' => 1000000,
			'#default_value' => isset($_GET[ 'min_price']) ? $_GET['min_price'] : '',
		);

		$form ['filters']['max_price'] = array(
			'#type' => 'number',
			'#placeholder' => t('Max'),
			'#min' => 0,
			'#max' => 1000000,
			'#default_value' => isset($_GET[ 'max_price']) ? $_GET['max_price'] : '',
		);

		return $form;
	}

	private function buildContent () {

		$query = \Drupal::database()->select( 'node_field_data', 'n' );
		$query->condition( 'n.type', 'furniture', '=' );

		$query->innerJoin('node__field_price', 'fp', 'fp.entity_id = n.nid' );

		$query->innerJoin('node__body', 'b', 'b.entity_id = n.nid');

		$query->innerJoin('node__field_fur_image', 'fi', 'fi.entity_id = n.nid');

		$query->innerJoin('node__field_categories', 'fc', 'fc.entity_id = n.nid' );

		$query->innerJoin('taxonomy_term_field_data', 't', 'fc.field_categories_target_id = t.tid' );

		$query->innerJoin('node_counter', 'nc', 'nc.nid = n.nid');

		if (!empty($_GET['title'])) {
			$query->condition('title', $_GET['title'], 'LIKE');
		}

		$query->addField('n', 'nid');
		$query->addField('n', 'nid', 'id');
		$query->addField('n', 'status');
		$query->addField('n', 'title');
		$query->addField('t', 'tid');
		$query->addField('t', 'name', 'taxonomy_name');
		$query->addField('fi', 'field_fur_image_target_id', 'image');
		$query->addField('fp', 'field_price_value', 'price');
		$query->addField('b', 'body_value', 'body');

		$query = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(25);

		$sort = 0;
		if (isset($_GET['sort'])) {
			$sort = $_GET['sort'];
		}

		if ($sort == 0) {
			$query->orderBy('nid', 'ASC');
		} else if ($sort == 1) {
			$query->orderBy('totalcount', 'DESC');
		} else if ($sort == 2) {
			$query->leftJoin('node__field_reviews', 'fr', 'fr.entity_id = n.nid');
			$query->leftJoin('comment_entity_statistics', 'ces', 'fr.entity_id = ces.entity_id');
			$query->leftJoin('comment_field_data', 'cfd', 'ces.entity_id = cfd.entity_id');
			$query->leftJoin('comment__field_your_rating', 'cff', 'cfd.cid = cff.entity_id');
			$query->orderBy( 'field_your_rating_rating', 'DESC' );
		} else if ($sort == 3) {
			$query->orderBy('nid', 'DESC');
		} else if ($sort == 4) {
			$query->orderBy('price', 'ASC');
		} else if ($sort == 5) {
			$query->orderBy('price', 'DESC');
		}

		# Range Filter
		$min_price = $_GET['min_price'];
		if (isset($min_price)) {
			$query->condition( 'fp.field_price_value', $min_price, '>=' );
		}

		$max_price = $_GET['max_price'];
		if (!empty($max_price)) {
			if (isset($max_price)) {
				$query->condition('fp.field_price_value', $max_price, '<=');
			}
		}

		$data = [];

		$results = $query->execute()->fetchAll(\PDO::FETCH_GROUP);

		foreach($results as $key => $result ) {
			$entry = [];
			foreach( $result as $node ) {
				$file = File::load($node->image);
				$url = \Drupal\image\Entity\ImageStyle::load('furniture_teaser_img')->buildUrl($file->getFileUri());
				$alias_node = \Drupal::service('path.alias_manager')->getAliasByPath('/node/' . $key);
				$alias_taxonomy = \Drupal::service('path.alias_manager')->getAliasByPath('/' . $node->tid);

				$alias_tax = str_replace(' ', '-', $alias_taxonomy);

				$entry['id'] = $node->id;
				$entry['nid'] = $alias_node;
				$entry['tid'] = $alias_tax;
				$entry['title'] = $node->title;
				$entry['body'] = substr(strip_tags(str_replace(array("\r", "\n"), '', $node->body)), 0, 400);
				$entry['price'] = $node->price;

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
		}

		$data['counters'] = count($data);

		return $data;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateForm ( array &$form, FormStateInterface $form_state ) {
		parent::validateForm( $form, $form_state );
	}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm ( array &$form, FormStateInterface $form_state ) {
		// Display result.
		foreach ( $form_state->getValues() as $key => $value ) {
			drupal_set_message( $key . ': ' . $value );
		}

	}
}
