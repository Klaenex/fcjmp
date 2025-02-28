<?php
/* Template Name: Page de Connexion */
get_header();
?>

<div class="login-form-container">
    <?php
    if (is_user_logged_in()) {
        echo '<p>Vous êtes déjà connecté.</p>';
    } else {
        if (isset($_GET['login']) && $_GET['login'] == 'failed') {
            echo '<p class="error">Identifiant ou mot de passe incorrect.</p>';
        }
    ?>
        <form method="post" action="<?php echo wp_login_url(); ?>">
            <label for="username">Nom d'utilisateur ou Email</label>
            <input type="text" name="log" id="username" required>

            <label for="password">Mot de passe</label>
            <input type="password" name="pwd" id="password" required>

            <input type="submit" value="Se connecter">
        </form>
        <a href="<?php echo wp_lostpassword_url(); ?>">Mot de passe oublié ?</a>
    <?php } ?>
</div>

<?php get_footer(); ?>