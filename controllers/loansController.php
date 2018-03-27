<?php
/* ----------------------------------------------------------------------
 * plugins/statisticsViewer/controllers/StatisticsController.php :
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2010 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This source code is free and modifiable under the terms of
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 *
 * ----------------------------------------------------------------------
 */

require_once(__CA_LIB_DIR__.'/core/Configuration.php');
require_once(__CA_MODELS_DIR__.'/ca_lists.php');
require_once(__CA_MODELS_DIR__.'/ca_objects.php');
require_once(__CA_MODELS_DIR__.'/ca_object_representations.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_users.php');
require_once(__CA_MODELS_DIR__.'/ca_object_checkouts.php');

class loansController extends ActionController {
    # -------------------------------------------------------
    protected $opo_config;		// plugin configuration file

    # -------------------------------------------------------
    # Constructor
    # -------------------------------------------------------

    public function __construct(&$po_request, &$po_response, $pa_view_paths=null) {
        parent::__construct($po_request, $po_response, $pa_view_paths);

        $this->opo_config = Configuration::load(__CA_APP_DIR__.'/plugins/loanInterface/conf/loanInterface.conf');
    }

    # -------------------------------------------------------
    # Functions to render views
    # -------------------------------------------------------
    public function loans() {
        $vs_user_idno=$this->request->getParameter('user', pString);
        $va_user = new ca_users();
        $loaded = $va_user->load($vs_user_idno);
        if(!$loaded) {
            // Redirect to index page with an error
        }

        $vs_object_idno=$this->request->getParameter('idno', pString);
        $va_object = new ca_objects();
        if($vs_object_idno && $va_object->load(array('idno' => $vs_object_idno))) {
            $transaction = ca_object_checkouts::newCheckoutTransaction();
            $result = $transaction->checkout($va_object->get('ca_objects.object_id'), $va_user->get('ca_users.user_id'));
            if(!$result) die("Prêt impossible");
        }

        $this->view->setVar("user", $va_user);
        $va_checkouts = new ca_object_checkouts();
        $this->render('loans_html.php');
    }

    public function index() {
        $this->render('index_html.php');
    }

    public function returns() {
        $vs_object_idno=$this->request->getParameter('idno', pString);
        $t_object = new ca_objects();
        $message = "";
        $error = "";
        if($vs_object_idno && $t_object->load(array('idno' => $vs_object_idno))) {
            $vn_object_id = $t_object->get('ca_objects.object_id');
            $t_checkout = new ca_object_checkouts();
            $objectIsOut = $t_checkout->objectIsOut($vn_object_id);
            $checkout_id = $objectIsOut["checkout_id"];
            $t_checkout->load($checkout_id);
            try {
                $t_checkout->checkin($vn_object_id, "note", array('request' => $this->request));

                $t_user = new ca_users($t_checkout->get('user_id'));
                $vs_user_name = $t_user->get('ca_users.fname').' '.$t_user->get('ca_users.lname');
                $vs_borrow_date = $t_checkout->get('ca_object_checkouts.checkout_date', array('timeOmit' => true));

                if ($t_checkout->numErrors() == 0) {
                    $message = '<em>'.$t_object->get('ca_objects.preferred_labels.name').'</em> ('.$t_object->get('ca_objects.idno').') <b>RENDU</b>' ;
                } else {
                    $error = 'Could not check in <em>'.$t_object->get('ca_objects.preferred_labels.name').'</em> ('.$t_object->get('ca_objects.idno').'): '.join("; ", $t_checkout->getErrors());
                }
            } catch (Exception $e) {
                $error = '<em>'.$t_object->get('ca_objects.preferred_labels.name').'</em> ('.$t_object->get('ca_objects.idno').") n'est pas emprunté.";
            }
            //$result = $t_checkout->checkin($id*1);
            //if(!$result) die("Retour de l'objet impossible");
        } else {
            if($vs_object_idno) $message = "L'identifiant ne correspond pas à un objet dans la base.";
        }
        $this->view->setVar("message", $message);
        $this->view->setVar("error", $error);
        $this->render('returns_html.php');
    }

    # -------------------------------------------------------
}
?>