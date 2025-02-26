<?php
/*
Template Name: Page des Membres
*/

get_header();
?>

<div class="wrap">
    <h1>Membres de la FCJMP</h1>
    <table class="member-table">
        <thead>
            <tr>
                <th>Employé</th>
                <th>Email</th>
                <th>Localisation</th>
                <th>Téléphone</th>
                <th>Coordonnateur</th>
            </tr>
        </thead>
        <tbody id="memberList">
            <!-- Les membres seront affichés ici -->
        </tbody>
    </table>
</div>

<?php
get_footer();
?>