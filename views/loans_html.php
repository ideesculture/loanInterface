<?php
    $va_user = $this->getVar("user");
?>

<h1><small>Prêts</small> <?php print $va_user->get("fname"); ?> <?php print $va_user->get("lname"); ?></h1>
<p>Login interface publique : <?php print $va_user->get("user_name"); ?></p>
<p><?php print ($email=$va_user->get("email") ? "<a href='mailto:$email'>$email</a>" : ""); ?></p>
<h4>Prêter</h4>
<form method="post" action="<?php print __CA_URL_ROOT__."/index.php/loanInterface/loans/loans"; ?>">
    <input type="hidden" name="user" value="<?php print $va_user->get('ca_users.user_name'); ?>"/>
    <input type="text" name="idno" width="60"/>
    <input type="submit"/>
</form>
<?php
$pn_user_id = $va_user->get("ca_users.user_id");
if(is_array($va_checkouts = ca_object_checkouts::getOutstandingCheckoutsForUser($pn_user_id, "<tr><td><unit relativeTo='ca_objects'><l>^ca_objects.preferred_labels.name</l> (^ca_objects.idno)</unit></td><td><em>Retour prévu ^ca_object_checkouts.due_date%timeOmit=1</em></td></tr>"))
    && (sizeof($va_checkouts) > 0)){
        print "<h4>Liste des prêts</h4>\n";
        print "<table class='loan_table'>";
        foreach($va_checkouts as $va_checkout) {
            print "<tr>".$va_checkout['_display']."</tr>\n";
        }
        print "</table>\n";
    }

?>
<style>
    .loan_table {
        width: 100%;
    }
    .loan_table td {
        border:1px solid lightgrey;
        padding:4px 10px;
        width: 45%;
    }
</style>
<script>
    jQuery(document).ready(function() {
       jQuery("#idno").focus();
    });
</script>