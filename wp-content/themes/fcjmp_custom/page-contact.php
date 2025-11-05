<?php
/*
Template Name: Contact
*/

require_once __DIR__ . '/../../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
$googleMapsApiKey = $_ENV['API_MAPS'];

get_header();
?>
<script async src="https://maps.googleapis.com/maps/api/js?key=<?php echo $googleMapsApiKey; ?>&callback=console.debug&libraries=maps,marker&v=beta"></script>
<section class="section section-green ">
    <div class="section-green_wrap">
        <h1 class="title title-big">Contact</h1>
    </div>
</section>
<section class="section section-contact">
    <div class="content content-contact">
        <div class="wrapper wrapper-contact">
            <div class="contact-info">
                <p><strong>Adresse :</strong> Rue Saint-Ghislain 26, 1000 Bruxelles, Belgique</p>
                <p><strong>Téléphone :</strong> <a href="tel:+3225136448">02/513.64.48</a></p>
                <p><strong>Email :</strong> <a href="mailto:infos@fcjmp.be">infos@fcjmp.be</a></p>
                <p><strong>Horaire :</strong> Du lundi au vendredi de 09h à 18h</p>
                <ul class="list list-contact">
                    <li><a href="https://www.facebook.com/FCJMP/?locale=fr_FR"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo/facebook.png" alt="" loading="lazy"></a></li>
                    <li><a href="https://www.instagram.com/fcjmp_asbl/"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo/instagram.png" alt="" loading="lazy"></a></li>
                </ul>
            </div>
            <gmp-map center="50.8393669128418,4.3469672203063965" zoom="14" map-id="FCJMP_MAP">
                <gmp-advanced-marker position="50.8393669128418,4.3469672203063965" title="My location"></gmp-advanced-marker>
            </gmp-map>
        </div>

        <ul class="wrapper wrapper-equipe">
            <li class="equipe">
                <img src="" alt="">
                <p class="nom">Pierre Evrard</p>
                <p>Directeur</p>
                <div>
                    <a href="mailto:pierre.evrard@fcjmp.be"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/mail.svg" alt=""></a>
                </div>
            </li>
            <li class="equipe"><img src="" alt="">
                <p></p>
                <p></p>
            </li>
            <li class="equipe"><img src="" alt="">
                <p></p>
                <p></p>
            </li>
            <li class="equipe"><img src="" alt="">
                <p></p>
                <p></p>
            </li>
            <li class="equipe"><img src="" alt="">
                <p></p>
                <p></p>
            </li>
            <li class="equipe"><img src="" alt="">
                <p></p>
                <p></p>
            </li>
            <li class="equipe"><img src="" alt="">
                <p></p>
                <p></p>
            </li>
            <li class="equipe"><img src="" alt="">
                <p></p>
                <p></p>
            </li>
        </ul>


    </div>

</section>

<?php get_footer(); ?>