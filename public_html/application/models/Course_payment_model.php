<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Course_payment_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /*
    This is used to add new record for payment
    */
    public function add($data) {
		$this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->insert('online_course_payment', $data);
       
		$id        = $this->db->insert_id();
        $message   = INSERT_RECORD_CONSTANT . " On online course payment id" . $id;
        $action    = "Insert";
        $this->log($message, $id, $action);
		$this->db->trans_complete(); # Completing transaction
        /* Optional */

        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            return $id;
        }
    }
}