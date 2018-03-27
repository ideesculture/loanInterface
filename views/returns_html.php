<?php
    $message = $this->getVar("message");
    $error = $this->getVar('error');
?>
<h1>Retours</h1>

<?php if($message) : ?>
<div class="notification-info-box rounded"><ul class="notification-info-box"><li class="notification-info-box"><?php print $message; ?></li>
    </ul>
</div>
<?php endif; ?>

<?php if($error) : ?>
    <div class="notification-error-box rounded"><ul class="notification-error-box"><li class="notification-error-box"><?php print $error; ?></li>
        </ul>
    </div>
<?php endif; ?>

<form method="get" action="<?php print __CA_URL_ROOT__; ?>/index.php/loanInterface/loans/returns">
    <input type="text" name="idno" width="50"/>
    <input type="submit"/>
</form>
