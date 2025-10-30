<?php
/*
Plugin Name: SMTP (FCJMP)
Description: Configuration SMTP pour forcer l'envoi via Infomaniak.
Author: FCJMP
*/

add_action('phpmailer_init', function ($phpmailer) {
    $phpmailer->isSMTP();
    $phpmailer->Host       = 'mail.infomaniak.com';
    $phpmailer->Port       = 587;            // 465 si SSL
    $phpmailer->SMTPSecure = 'tls';          // 'ssl' si 465
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Username   = 'no-reply@fcjmp.be';  // ton adresse d’envoi
    $phpmailer->Password   = 'djd746K4no-reply';   // mot de passe de cette boîte
    $phpmailer->From       = 'no-reply@fcjmp.be';
    $phpmailer->FromName   = 'FCJMP';
});
