<?php
/**
 * Created by PhpStorm.
 * User: veus
 * Date: 11/24/17
 * Time: 9:54 AM
 */

namespace Drupal\subscribe\Form;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\HtmlCommand;

class SubscribeForm extends FormBase
{
    public function getFormId()
    {
        return 'subscribe_form_block';

    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['details'] = [
            '#markup' => '<div class="sub-info"><h3>Subscribe for More Updates</h3><p class="subtittle">Get instant updates about our new products and special promos!</p></div>',

        ];
        $form['email'] = array(
            '#type' => 'email',
            '#placeholder' => 'E-mail',
            '#size'  => 32,
            '#required' => false,
        );

        $form['actions'] = array(
            '#type' => 'submit',
            '#value' => t('Subscribe'),

            '#ajax' => array(
                'callback' => '::ajaxFormSubmit',
                'event' => 'click',
                'progress' => array(
                    'type' => 'throbber',
                    'message' => 'Checking your Email. Please wait!',
                ),
            ),
            $form_state->setCached(FALSE),
        );
        $form[ 'message' ] = [
            '#type'       => 'container',
            '#attributes' => [
                'id' => 'subscribe-message',
            ],
        ];


        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $email = $form_state->getValue('title');
        if (!\Drupal::service('email.validator')->isValid($email)) {
            $form_state->setErrorByName('title', $this->t('The email address appears to be invalid.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function validateEmail(array &$form, FormStateInterface $form_state) {
        if (substr($form_state->getValue('email'), -4) !== '.com') {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {

    }

    public function ajaxFormSubmit(array $form, FormStateInterface $form_state)
    {
        $values = $form_state->getValue('email');
        $valid = $this->validateEmail($form, $form_state);
        $response = new AjaxResponse();

        $email = \Drupal::database()->select('subscribe', 's');
        $email->condition('s.email', $values, '=');
        $email->addField('s', 'email');
        $emails = $email->execute()->fetchAll();

        if (empty($emails)) {
            $insert = \Drupal::database()->insert('subscribe');
            $insert->fields([
                'email',
                'date',
            ]);
            $insert->values([
                $values,
                date('F d, Y'),
            ]);

            $insert->execute();

            $item = [
                '#type'       => 'container',
                '#attributes' => [
                    'id'    => 'subscribe-message',
                    'class' => 'success',
                ],
                '#markup'     => "Check your inbox or spam folder now to confirm your subscription.",
            ];

        } else {
            $item = [
                '#type'       => 'container',
                '#attributes' => [
                    'id'    => 'subscribe-message',
                    'class' => 'fail',
                ],
                '#markup'     => "Email is not valid or exists in our database. Please type the correct email address!",
            ];
        }
        $renderer = \Drupal::service( 'renderer' );
        $response = new AjaxResponse();
        $item = $renderer->render( $item );
        $response->addCommand( new ReplaceCommand( '#subscribe-message', $item ) );

        return $response;
    }
}

