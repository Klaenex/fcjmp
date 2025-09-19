<?php
if (!defined('ABSPATH')) {
    exit;
}

/** Tabs helper */
function fcjmp_mm_active_tab()
{
    // support POST (submit) et GET (navigation onglets)
    $t = isset($_POST['tab']) ? sanitize_text_field($_POST['tab']) : sanitize_text_field($_GET['tab'] ?? 'general');
    // Onglets: Général (inclut ex-Page), Accès, Planification, Prévisualisation
    $allowed = ['general', 'access', 'schedule', 'preview'];
    return in_array($t, $allowed, true) ? $t : 'general';
}

/** Enqueue CSS admin uniquement sur notre page */
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook === 'settings_page_fcjmp-mm') {
        wp_enqueue_style('fcjmp-mm-admin', fcjmp_mm_asset_url('assets/css/admin.css'), [], FCJMP_MM_VER);
    }
});

/** Menu + register setting */
add_action('admin_menu', function () {
    add_options_page(
        'Mode maintenance',
        'Mode maintenance',
        'manage_options',
        'fcjmp-mm',
        'fcjmp_mm_render_settings_page'
    );
});
add_action('admin_init', function () {
    register_setting('fcjmp_mm_group', 'fcjmp_mm_options', [
        'type'              => 'array',
        'sanitize_callback' => 'fcjmp_mm_sanitize_options',
        'default'           => fcjmp_mm_defaults(),
    ]);
});

/** Sanitize par onglet (Page fusionné dans Général, pas d’onglet Outils, pas de preview front) */
function fcjmp_mm_sanitize_options($input)
{
    $d   = fcjmp_mm_defaults();
    $out = fcjmp_mm_get_all_options();
    $tab = fcjmp_mm_active_tab();

    if ($tab === 'general') {
        // Réglages généraux + (ex-Page)
        $out['enabled']         = !empty($input['enabled']);
        $out['retry_after_min'] = max(1, intval($input['retry_after_min'] ?? $d['retry_after_min']));

        $out['form_url']        = esc_url_raw(trim((string)($input['form_url'] ?? $d['form_url'])));
        $out['contact_email']   = sanitize_email($input['contact_email'] ?? $d['contact_email']);
        $out['custom_message']  = sanitize_text_field($input['custom_message'] ?? $d['custom_message']);
    }

    if ($tab === 'access') {
        $out['allow_rest']         = !empty($input['allow_rest']);
        $out['allow_login_admin']  = !empty($input['allow_login_admin']);
        $out['allow_admins_front'] = !empty($input['allow_admins_front']);

        // IPs
        $ips_raw = trim((string)($input['whitelist_ips_textarea'] ?? ''));
        $ips = preg_split('/\r\n|\r|\n/', $ips_raw);
        $ips = array_filter(array_map('trim', (array)$ips), fn($ip) => filter_var($ip, FILTER_VALIDATE_IP));
        $out['whitelist_ips'] = array_values(array_unique($ips));

        // Users cochés
        $user_ids = array_map('intval', (array)($input['whitelist_users'] ?? []));
        $user_ids = array_values(array_filter($user_ids, fn($id) => $id > 0));
        $out['whitelist_users'] = $user_ids;
    }

    if ($tab === 'schedule') {
        $mode = in_array(($input['schedule_mode'] ?? 'off'), ['off', 'window'], true) ? $input['schedule_mode'] : 'off';
        $out['schedule_mode']  = $mode;
        $out['schedule_start'] = trim((string)($input['schedule_start'] ?? ''));
        $out['schedule_end']   = trim((string)($input['schedule_end'] ?? ''));
    }

    // Onglet preview: pas de sauvegarde.

    return $out;
}

/** Rendu de la page (Page fusionnée dans Général, Preview en admin uniquement) */
function fcjmp_mm_render_settings_page()
{
    if (!current_user_can('manage_options')) return;

    $tab     = fcjmp_mm_active_tab();
    $opts    = fcjmp_mm_get_all_options();
    $roles   = wp_roles()->roles;
    $enabled = fcjmp_mm_is_effectively_enabled();

    // Bouton toggle (gros)
    $action  = $enabled ? 'off' : 'on';
    $label   = $enabled ? 'Désactiver la maintenance' : 'Activer la maintenance';
    $toggle_url = wp_nonce_url(admin_url('admin-post.php?action=fcjmp_mm_toggle&set=' . $action), 'fcjmp_mm_toggle');

    // Données pour onglets
    $ips_text   = implode("\n", (array)$opts['whitelist_ips']);
    $current_ip = esc_html(fcjmp_mm_get_ip());

    // Utilisateurs groupés par rôle
    $all_users = get_users(['fields' => 'all_with_meta', 'orderby' => 'display_name', 'order' => 'ASC']);
    $users_by_role = [];
    foreach ($all_users as $u) {
        $u_roles = is_array($u->roles ?? null) ? $u->roles : [];
        $main = $u_roles ? $u_roles[0] : 'autres';
        $users_by_role[$main][] = $u;
    }
    $role_order = array_keys($roles);
    foreach (array_keys($users_by_role) as $rk) if (!in_array($rk, $role_order, true)) $role_order[] = $rk;
    $role_names = [];
    foreach ($role_order as $rk) $role_names[$rk] = $roles[$rk]['name'] ?? ucfirst($rk);

?>
    <div class="wrap">
        <h1>Mode maintenance</h1>

        <p class="fcjmp-admin-status">
            <a class="button button-primary button-hero" href="<?php echo esc_url($toggle_url); ?>">
                <?php echo esc_html($label); ?>
            </a>
            &nbsp;&nbsp;
            <strong>Status :</strong>
            <?php if ($enabled): ?>
                <span class="fcjmp-admin-on">⚠️ Maintenance ACTIVE</span>
            <?php else: ?>
                <span class="fcjmp-admin-off">✓ Maintenance INACTIVE</span>
            <?php endif; ?>
        </p>

        <h2 class="nav-tab-wrapper">
            <a class="nav-tab <?php echo $tab === 'general' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('options-general.php?page=fcjmp-mm&tab=general')); ?>">Général</a>
            <a class="nav-tab <?php echo $tab === 'access' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('options-general.php?page=fcjmp-mm&tab=access')); ?>">Accès</a>
            <a class="nav-tab <?php echo $tab === 'schedule' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('options-general.php?page=fcjmp-mm&tab=schedule')); ?>">Planification</a>
            <a class="nav-tab <?php echo $tab === 'preview' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('options-general.php?page=fcjmp-mm&tab=preview')); ?>">Prévisualisation</a>
        </h2>

        <?php if ($tab === 'preview'): ?>
            <?php
            // HTML complet de la page maintenance, rendu dans un iframe via srcdoc
            $html = fcjmp_mm_get_maintenance_html(true);
            $srcdoc = esc_attr($html);
            ?>
            <div class="fcjmp-preview-toolbar">
                <label for="fcjmp-prev-width"><strong>Largeur d’aperçu :</strong></label>
                <select id="fcjmp-prev-width">
                    <option value="375">Mobile — 375 px</option>
                    <option value="768">Tablette — 768 px</option>
                    <option value="1024">Petit desktop — 1024 px</option>
                    <option value="1280" selected>Bureau — 1280 px</option>
                    <option value="full">Plein écran (100%)</option>
                </select>
            </div>

            <div class="fcjmp-preview-wrap">
                <iframe id="fcjmp-preview"
                    class="fcjmp-preview-iframe"
                    srcdoc="<?php echo $srcdoc; ?>"
                    title="Aperçu de la page de maintenance"></iframe>
            </div>

            <script>
                (function() {
                    const sel = document.getElementById('fcjmp-prev-width');
                    const frame = document.getElementById('fcjmp-preview');

                    function applyWidth() {
                        const v = sel.value;
                        if (v === 'full') {
                            frame.style.maxWidth = '100%';
                        } else {
                            frame.style.maxWidth = v + 'px';
                        }
                    }
                    sel.addEventListener('change', applyWidth);
                    applyWidth();
                })();
            </script>
        <?php else: ?>

            <form method="post" action="options.php">
                <?php settings_fields('fcjmp_mm_group'); ?>
                <input type="hidden" name="tab" value="<?php echo esc_attr($tab); ?>"><!-- conserver l’onglet au submit -->

                <?php if ($tab === 'general'): ?>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row">Activer</th>
                            <td><label><input type="checkbox" name="fcjmp_mm_options[enabled]" <?php checked($opts['enabled']); ?>> Activer le mode maintenance</label></td>
                        </tr>
                        <tr>
                            <th scope="row">Retry-After (minutes)</th>
                            <td><input type="number" name="fcjmp_mm_options[retry_after_min]" min="1" value="<?php echo esc_attr($opts['retry_after_min']); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row">Lien d'inscription (formulaire)</th>
                            <td><input type="url" name="fcjmp_mm_options[form_url]" class="regular-text" value="<?php echo esc_attr($opts['form_url']); ?>" /></td>
                        </tr>
                        <tr>
                            <th scope="row">Email de contact</th>
                            <td><input type="email" name="fcjmp_mm_options[contact_email]" class="regular-text" value="<?php echo esc_attr($opts['contact_email']); ?>" /></td>
                        </tr>
                        <tr>
                            <th scope="row">Message personnalisé</th>
                            <td><textarea name="fcjmp_mm_options[custom_message]" rows="4" cols="60"><?php echo esc_textarea($opts['custom_message']); ?></textarea></td>
                        </tr>
                    </table>
                <?php endif; ?>

                <?php if ($tab === 'access'): ?>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row">REST API</th>
                            <td><label><input type="checkbox" name="fcjmp_mm_options[allow_rest]" <?php checked($opts['allow_rest']); ?>> Autoriser la REST API</label></td>
                        </tr>
                        <tr>
                            <th scope="row">wp-admin / wp-login</th>
                            <td><label><input type="checkbox" name="fcjmp_mm_options[allow_login_admin]" <?php checked($opts['allow_login_admin']); ?>> Autoriser l’accès admin/login</label></td>
                        </tr>
                        <tr>
                            <th scope="row">Administrateurs (front)</th>
                            <td><label><input type="checkbox" name="fcjmp_mm_options[allow_admins_front]" <?php checked($opts['allow_admins_front']); ?>> Autoriser les administrateurs à accéder au front pendant la maintenance</label></td>
                        </tr>
                        <tr>
                            <th scope="row">Whitelist IPs</th>
                            <td>
                                <p><em>Une IP par ligne</em></p>
                                <textarea name="fcjmp_mm_options[whitelist_ips_textarea]" rows="6" cols="60"><?php echo esc_textarea($ips_text); ?></textarea>
                                <p>Votre IP détectée : <code><?php echo $current_ip; ?></code>
                                    <button class="button" type="button" onclick="
                                  (function(){
                                    const ta=document.querySelector('textarea[name=&quot;fcjmp_mm_options[whitelist_ips_textarea]&quot;]');
                                    if(ta && '<?php echo addslashes($current_ip); ?>'){
                                      ta.value = (ta.value ? ta.value + '\n' : '') + '<?php echo addslashes($current_ip); ?>';
                                    }
                                  })();
                                ">Ajouter mon IP</button>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Comptes autorisés</th>
                            <td>
                                <p class="description">Cochez les utilisateurs qui auront accès au site pendant la maintenance.</p>
                                <div class="fcjmp-user-grid">
                                    <?php foreach ($role_order as $rk):
                                        $readable = $role_names[$rk] ?? $rk;
                                        $users = $users_by_role[$rk] ?? [];
                                        if (!$users) continue;
                                    ?>
                                        <fieldset class="fcjmp-user-group">
                                            <legend><strong><?php echo esc_html($readable); ?></strong></legend>
                                            <?php foreach ($users as $u):
                                                $checked = in_array($u->ID, (array)$opts['whitelist_users'], true);
                                            ?>
                                                <label class="fcjmp-user">
                                                    <input type="checkbox" name="fcjmp_mm_options[whitelist_users][]" value="<?php echo esc_attr($u->ID); ?>" <?php checked($checked); ?>>
                                                    <?php echo esc_html($u->display_name); ?>
                                                    <span class="fcjmp-user-mail">&lt;<?php echo esc_html($u->user_email); ?>&gt;</span>
                                                </label><br />
                                            <?php endforeach; ?>
                                        </fieldset>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                <?php endif; ?>

                <?php if ($tab === 'schedule'): ?>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row">Mode de planification</th>
                            <td>
                                <select name="fcjmp_mm_options[schedule_mode]">
                                    <option value="off" <?php selected($opts['schedule_mode'], 'off'); ?>>Désactivé</option>
                                    <option value="window" <?php selected($opts['schedule_mode'], 'window'); ?>>Fenêtre (début/fin)</option>
                                </select>
                                <p class="description">Si activé, la maintenance sera active entre Début et Fin, même si le bouton principal est OFF.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Début</th>
                            <td>
                                <input type="datetime-local" name="fcjmp_mm_options[schedule_start]" value="<?php echo esc_attr(str_replace(' ', 'T', $opts['schedule_start'])); ?>" />
                                <p class="description">Format: YYYY-MM-DD HH:MM (timezone WordPress)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Fin</th>
                            <td><input type="datetime-local" name="fcjmp_mm_options[schedule_end]" value="<?php echo esc_attr(str_replace(' ', 'T', $opts['schedule_end'])); ?>" /></td>
                        </tr>
                    </table>
                <?php endif; ?>

                <?php submit_button(); ?>
            </form>
        <?php endif; ?>
    </div>
<?php
}
