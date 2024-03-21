<?php

/**
 * @file
 * Contains the RSVP Enabler service.
 */

namespace Drupal\rsvplist;

use Drupal\Core\Database\Connection;
use Drupal\node\Entity\Node;

class EnablerService {
    protected $database_connection;

    public function __construct(Connection $connection) {
        $this->database_connection = $connection;
    }

    /**
     * Checks if an individual node is RSVP enabled
     * @param  Node $node
     * @return bool
     * whether or not the node is enabled for the RSVP functionality
     */
    public function isEnabled(Node &$node){
        if($node->isNew()){
            return False;
        }
        try{
            $select_enabled = $this->database_connection->select('rsvplist_enabled',"re");
            $select_enabled->fields("re", ["nid"]);
            $select_enabled->condition("nid", $node->id());
            $result = $select_enabled->execute();

            if($node->getType()=='event'){
                $seats_left = $node->get('field_event_date');
                // dump($seats_left);
            }
            

            return !(empty($result->fetchCol()));
        }
        catch(\Exception $e){
            \Drupal::messenger()->addError(
                t('Unable to determine RSVP settings at this time. please try again later.')
            );
            return NULL;
        }
    }

    /**
     * Sets an individual node to be RSVP enabled
     * @param Node $node
     * @throws Exception
     */
    public function setEnabled(Node $node){
        try {
            if(!($this->isEnabled($node))){
                $insert = $this->database_connection->insert('rsvplist_enabled');
                $insert->fields(['nid']);
                $insert->values([$node->id()]);
                $insert->execute();
            }
        }
        catch(\Exception $e){
            \Drupal::messenger()->addError(
                t('Unable to save RSVP settings at this time. Please try again.')
            );
        }
    }
    /**
     * Delete RSVP enabled settings for an individual node
     * @param Node $node
     */
    public function delEnabled(Node $node){
        try {
            $delete = $this->database_connection->delete('rsvplist_enabled');
            $delete->condition('nid', $node->id());
            $delete->execute();
        }
        catch(\Exception $e){
            \Drupal::messenger()->addError(
                t('Unable to save RSVP settings at this time. Please try again.')
            );
        }
    }
}