<?php

/**
 * @file
 * Provide site administrators with a list of all the RSVP List signups 
 * so they know who is attending their events.
 */

 namespace Drupal\rsvplist\Controller;

 use Drupal\Core\Controller\ControllerBase;
 use Drupal\Core\Database\Database;

 class ReportController extends ControllerBase {
    /**
     * Gets and returns all RSVPs for all nodes
     * These are returned as an associative array, with each row
     * containing the username, the node title, and emailof RSVP
     * 
     * @return array|null
     */
    protected function load() {
        try{
            $database = \Drupal::database();
            $select_query = $database->select('rsvplist','r');

            //Join the user table, so we can get the entry creator's username
            $select_query->join('users_field_data','u','r.uid = u.uid');
            // Join the node table, so we can get the event's name
            $select_query->join('node_field_data','n','r.nid = n.nid');

            // select these specific fields for the output
            $select_query->addField('u','name','username');
            $select_query->addField('n','title');
            $select_query->addField('r','name');
            $select_query->addField('r','mail');

            // fetchAll() and fetchAllAssoc will by default fetch using
            // whatever fetch node was set on the query
            $entries = $select_query->execute()->fetchAll(\PDO::FETCH_ASSOC);

            return $entries;
        } catch(\Exception $e) {
            \Drupal::messenger()->addStatus(
                t('Unable to access the database. Please try again.')
            );
            return null;
        }
    }

    /**
     * Creates the RSVPList report page
     * @return array
     * Render array for the RSVPList report output.
     */
    public function report() {
        $content = [];

        $content['message'] = [
            '#markup'=> t('Bellow is a list of all Events RSVPs inclusing username, full name, email address and the name of the event they will be attending.'),
        ];

        $headers = [
            t('Username'),
            t('Event'),
            t('full name'),
            t('Email'),
        ];

        //because load() method returns an associative array with each table row
        //as its own array, we can simply define the html table rows :
        $table_rows = $this->load();

        // //if load() did not return the results in a structure compatible with what we need, 
        // //we could populate the $table_rows variable : 
        // $table_rows = [];
        // //load the entries from database
        // $rsvp_entries = $this->load();
        // //go through each entry and add it to $rows
        // //each array will be rendered as a row in an html table.
        // foreach($rsvp_entries as $entry) {
        //     $table_rows[] = $entry;
        // }


        //Create the render array for rendering an html table.
        $content['table'] = [
            '#type'=> 'table',
            '#header'=> $headers,
            '#rows'=> $table_rows,
            '#empty'=>t('No entries available.')
        ];

        // Do not cache this page (to not use cache , we want drupal to always refresh this render array when it is time to display it)
        $content['#cache']['max-age'] = 0;

        return $content;
    }
 }