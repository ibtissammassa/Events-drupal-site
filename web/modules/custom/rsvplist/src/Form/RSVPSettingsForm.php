<?php

/**
 * @file
 * Contains the settings for administering the RSVP Form
 */

 namespace Drupal\rsvplist\Form;

 use Drupal\Core\Form\ConfigFormBase;
 use Drupal\Core\Form\FormStateInterface;

 class RSVPSettingsForm extends ConfigFormBase {
    /**
     * {@inheritDoc}
     */
    public function getFormId(){
        return 'rsvplist_admin_settings';
    }
    /**
     * {@inheritDoc}
     */
    protected function getEditableConfigNames(){// returns an array of the configuration names that will be editable
        return [
            'rsvplist.settings',
        ];
    }
    /**
     * {@inheritDoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $types = node_type_get_names();//returns the node content types currently existing on the site (array of strings of the labels of the content types)
        $config = $this->config('rsvplist.settings');
        $form['rsvplist_types'] = [
            '#type' => 'checkboxes',
            '#title'=> $this->t('The content types to enable RSVP collection for'),
            '#default_value' => $config->get('allowed_types'),
            '#options' => $types,
            '#description'=> $this->t('On the specified node types, an RSVP option will be available and can be enabled while the node is being edited.'),
        ];

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritDoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $selected_allowed_types = array_filter($form_state->getValue('rsvplist_types'));
        sort($selected_allowed_types);

        $this->config('rsvplist.settings')
          ->set('allowed_types', $selected_allowed_types)
          ->save();

        parent::submitForm($form, $form_state);
    }
}