<?php
/*
Template Name: Contact
*/
get_header();
?>
<script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBdGWiRBBvwnl-9DHpekSiEdDhxih0X_rg&callback=console.debug&libraries=maps,marker&v=beta">
</script>
<section class="section section-contact">
    <div class="content content-contact">
        <h1 class="title tile-big">Contact</h1>
        <div class="wrapper wrapper-contact">
            <div class="contact-info">
                <p><strong>Adresse :</strong> Rue Saint-Ghislain 26, 1000 Bruxelles, Belgique</p>
                <p><strong>Téléphone :</strong> <a href="tel:+3225136448">02/513.64.48</a></p>
                <p><strong>Email :</strong> <a href="mailto:infos@fcjmp.be">infos@fcjmp.be</a></p>
            </div>
            <gmp-map center="50.8393669128418,4.3469672203063965" zoom="14" map-id="DEMO_MAP_ID">
                <gmp-advanced-marker position="50.8393669128418,4.3469672203063965" title="My location"></gmp-advanced-marker>
            </gmp-map>
        </div>

    </div>

</section>


<div class="social-links">
    <h2>Suivez-nous</h2>
    <a href="https://www.facebook.com/FCJMP/" target="_blank">Facebook</a>
</div>



<?php get_footer(); ?>