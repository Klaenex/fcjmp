<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Retourne l'HTML complet de la page de maintenance (DOCTYPE + HEAD + BODY).
 * @param bool $is_preview  ajoute un libellé "(aperçu admin)" et n'affiche pas 503 dans le footer.
 * @return string
 */
function fcjmp_mm_get_maintenance_html($is_preview = false)
{
    $form_url      = esc_url(fcjmp_mm_get_option('form_url'));
    $contact_email = sanitize_email(fcjmp_mm_get_option('contact_email'));
    $message       = esc_html(fcjmp_mm_get_option('custom_message'));
    $logo_html     = get_custom_logo();
    $site_name     = get_bloginfo('name');
    $css_href      = esc_url(fcjmp_mm_asset_url('assets/css/maintenance.css'));

    ob_start();
?>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1" />
        <title>Site en maintenance<?php echo $is_preview ? ' — aperçu' : ''; ?></title>
        <link rel="preload" as="style" href="<?php echo $css_href; ?>" />
        <link rel="stylesheet" href="<?php echo $css_href; ?>" />
    </head>

    <body>
        <div class="fcjmp-wrap">
            <div class="fcjmp-card">
                <?php
                if ($logo_html) {
                    echo '<div class="fcjmp-logo">' . $logo_html . '</div>';
                } else {
                    echo '<h2>' . esc_html($site_name) . '</h2>';
                }
                ?>
                <div class="fcjmp-tag">Site en travaux<?php echo $is_preview ? ' (aperçu admin)' : ''; ?></div>
                <h1>Nous revenons très vite.</h1>
                <p class="fcjmp-text"><?php echo $message; ?></p>
                <p class="fcjmp-text">Pour les inscriptions à nos formations vous pouvez passer par ici :</p>
                <p><a class="fcjmp-btn" href="<?php echo $form_url; ?>" target="_blank" rel="noopener">S'inscrire aux formations</a></p>
                <p class="fcjmp-text">Et si vous avez besoin de nous contacter : <a class="fcjmp-link" href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a></p>
                <div class="fcjmp-footer"><?php echo $is_preview ? 'Aperçu — 200 OK' : 'HTTP 503'; ?></div>
            </div>
        </div>
    </body>

    </html>
<?php
    return (string) ob_get_clean();
}
