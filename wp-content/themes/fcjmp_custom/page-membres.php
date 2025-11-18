<?php
/*
 Template Name: Page des Membres
*/

get_header();
?>

<section class="section">


    <div class="content">
        <h1>Membres de la FCJMP</h1>

        <div class="filtre-regions">
            <label for="filterRegion">Filtrer par région :</label>
            <select id="filterRegion">
                <option value="all">Toutes</option>
                <option value="1">Bruxelles</option>
                <option value="2">Hainaut</option>
                <option value="3">Brabant Wallon</option>
                <option value="4">Liège</option>
                <option value="5">Luxembourg</option>
                <option value="6">Namur</option>
            </select>
        </div>

        <div class="region-wrapper" data-region="1">
            <h2>Bruxelles</h2>
            <div class="card" id="memberListBruxelles"></div>
        </div>

        <div class="region-wrapper" data-region="2">
            <h2>Hainaut</h2>
            <div class="card" id="memberListHainaut"></div>
        </div>

        <div class="region-wrapper" data-region="3">
            <h2>Brabant Wallon</h2>
            <div class="card" id="memberListBrabantWallon"></div>
        </div>

        <div class="region-wrapper" data-region="4">
            <h2>Liège</h2>
            <div class="card" id="memberListLiege"></div>
        </div>

        <div class="region-wrapper" data-region="5">
            <h2>Luxembourg</h2>
            <div class="card" id="memberListLuxembourg"></div>
        </div>

        <div class="region-wrapper" data-region="6">
            <h2>Namur</h2>
            <div class="card" id="memberListNamur"></div>
        </div>

    </div>
</section>

<?php
get_footer();
