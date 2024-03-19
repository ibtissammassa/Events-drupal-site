<?php

/**
 * @file
 * A form to collect an email address for RSVP details
 */

namespace Drupal\rsvplist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class RSVPForm extends FormBase {
    /**
     * {@inheritDoc}
     */
    function getFormId(){
        return 'rsvplist_email_form';
    }
    /**
     * {@inheritDoc}
     */
    function buildForm(array $form, FormStateInterface $form_state){
        // get the full loaded node object of the viewed page.
        // it gets the node from the parameter
        $node = \Drupal::routeMatch()->getParameter('node');

        //some pages may not be nodes, so the $node will be NULL
        if(!(is_null($node))) {
            $nid = $node -> id();
        }
        else{
            $nid = 0;
        }
        $form['name'] = [
            '#type'=> 'textfield',
            '#title' => t('Full Name'),
            '#size' => 25,
            '#description' => t('Enter your full name'),
            '#required' => TRUE,
        ];
        $form['email'] = [
            '#type' => 'textfield',
            '#title' => t('Email address'),
            '#size' => 25,
            '#description' => t('We will send updates to the email address you provide'),
            '#required' => TRUE,
        ];
        $form['submit'] = [
            '#type' => 'submit',
            '#value' => t('RSVP'),
        ];
        //this field contains the id of the node the form is displayed on
        $form['nid'] = [
            '#type' => 'hidden',
            '#value' => $nid,
        ];
        return $form;
    }
    /**
     * {@inheritDoc}
     */
    function submitForm(array &$form, FormStateInterface $form_state){
        // $submitted_email = $form_state->getValue('email');
        // $username = $form_state->getValue('name');
        // $this->messenger()->addMessage(t('Hi @name! the form is working :) you entered @email :3',
        //   ['@name' => $username,'@email' => $submitted_email]));
        try{
        /////Phase 1: initiate variables to save.
           
           //Get current user ID
           $uid = \Drupal::currentUser()->id();
            
           //Obtain values from form
           $nid = $form_state->getValue('nid');
           $name = $form_state->getValue('name');
           $email = $form_state->getValue('email');

           $current_time = \Drupal::time()->getRequestTime();
        /////End Phase 1

        /////Phase 2: Save the values to database
            $query = \Drupal::database()->insert('rsvplist');

            //specify the values that the query will insert into
            $query->fields([
                'uid',
                'nid',
                'name',
                'mail',
                'created',
            ]);

            //Set values of the fields selected
            $query->values([
                $uid,
                $nid,
                $name,
                $email,
                $current_time,
            ]);

            //execute the query
            $query->execute();
        /////End Phase 2

        /////Phase 3: Display a success msg
            \Drupal::messenger()->addMessage(
                t('Thanks for your RSVP @name, you are on the list for the event!',
                ['@name'=> $name]),
            );
        /////End Phase 3

        }catch(\Exception $e){
            \Drupal::messenger()->addError(
                t('Unable to save RSVP settings due to database error. Please try again.')
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(&$form, FormStateInterface $form_state){
        // this function is only to set errors at form elements
        $value = $form_state->getValue('email');
        // Drupal provides an email validator service
        if(!(\Drupal::service('email.validator')->isValid($value))){
            $form_state->setErrorByName('email',
            $this->t('It appears that %mail is not a valid email.
            Please try again', ['%mail'=>$value]));
        }
    }
}