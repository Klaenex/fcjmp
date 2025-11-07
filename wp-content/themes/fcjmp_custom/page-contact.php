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
        <h2 class="title title-big title-equipe">
            L’équipe communautaire
        </h2>
        <ul class="wrapper wrapper-equipe">

            <li class="equipe">
                <img src="https://picsum.photos/200/300.webp" alt="">
                <p class="name">Pierre Evrard</p>
                <p>Direction</p>
                <div>
                    <a href="mailto:pierre.evrard@fcjmp.be"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/mail.svg" alt=""></a>
                </div>
            </li>
            <li class="equipe"><img src="https://picsum.photos/200/300.webp" alt="">
                <p class="name">Ludovic Emmada</p>
                <p>Pédagogique</p>
                <div>
                    <a href="mailto:ludovic.emmada@fcjmp.be"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/mail.svg" alt=""></a>
                </div>
            </li>
            <li class="equipe"><img src="https://picsum.photos/200/300.webp" alt="">
                <p class="name">Eloïse Roekaerts</p>
                <p>Pédagogique</p>
                <div>
                    <a href="mailto:eloise.roekaerts@fcjmp.be"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/mail.svg" alt=""></a>
                </div>
            </li>
            <li class="equipe"><img src="https://picsum.photos/200/300.webp" alt="">
                <p class="name">Vincenzo Cuozzo</p>
                <p>Communication</p>
                <div>
                    <a href="mailto:vincent.cuozzo@fcjmp.be"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/mail.svg" alt=""></a>
                </div>
            </li>
            <li class="equipe"><img src="https://picsum.photos/200/300.webp" alt="">
                <p class="name">Nathalie Miecret</p>
                <p>Suivis de centres</p>
                <div>
                    <a href="mailto:nathalie.miecret@fcjmp.be"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/mail.svg" alt=""></a>
                    <a href="tel:0477884754"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/phone.svg" alt=""></a>
                </div>
            </li>
            <li class="equipe"><img src="https://picsum.photos/200/300.webp" alt="">
                <p class="name">Odile Jenkins</p>
                <p>Suivis de centres</p>
                <div>
                    <a href="mailto:odile.jenkins@fcjmp.be"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/mail.svg" alt=""></a>
                    <a href="tel:0477884751"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/phone.svg" alt=""></a>
                </div>
            </li>
            <li class="equipe"><img src="https://picsum.photos/200/300.webp" alt="">
                <p class="name">Benjamin Mignot</p>
                <p>Chargé de projet "Santé"</p>
                <div>
                    <a href="mailto:benjamin.mignot@fcjmp.be"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/mail.svg" alt=""></a>
                </div>
            </li>
            <li class="equipe"><img src="https://picsum.photos/200/300.webp" alt="">
                <p class="name">Emilie Bastin</p>
                <p>Secrétariat</p>
                <div>
                    <a href="mailto:emilie.bastin@fcjmp.be"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/mail.svg" alt=""></a>

                </div>
            </li>
            <li class="equipe"><img src="https://picsum.photos/200/300.webp" alt="">
                <p class="name">Georgios Tzoumacas </p>
                <p>Comptabilité</p>
                <div>
                    <a href="mailto:infos@fcjmp.be"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/mail.svg" alt=""></a>

                </div>
            </li>
            <li class="equipe"><img src="https://picsum.photos/200/300.webp" alt="">
                <p class="name">Guillaume Vanden Borre</p>
                <p>Comptabilité</p>
                <div>
                    <a href="mailto:infos@fcjmp.be"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/mail.svg" alt=""></a>

                </div>
            </li>
            <li class="equipe"><img src="https://picsum.photos/200/300.webp" alt="">
                <p class="name">Yenny Rojas Brinez</p>
                <p>Comptabilité</p>
                <div>
                    <a href="mailto:infos@fcjmp.be"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/mail.svg" alt=""></a>

                </div>
            </li>
            <li class="equipe"><img src="https://picsum.photos/200/300.webp" alt="">
                <p class="name">Aysima Kargin</p>
                <p>Comptabilité</p>
                <div>
                    <a href="mailto:infos@fcjmp.be"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/mail.svg" alt=""></a>

                </div>
            </li>
        </ul>


    </div>

</section>

<?php get_footer(); ?>