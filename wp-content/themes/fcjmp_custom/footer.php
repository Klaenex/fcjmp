<footer class="footer">
    <div class="wrapper-footer">
        <p>Avec le soutien de :</p>
        <ul class="list list-partnair">
            <li class="list-partnair-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo/cocof.png" alt="Logo Cocof" loading="lazy">
            </li>
            <li class="list-partnair-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo/actiris.png" alt="Logo Actiris" loading="lazy">
            </li>
            <li class="list-partnair-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo/fwb.png" alt="Logo Fédération Wallonie-Bruxelles" loading="lazy">
            </li>
            <li class="list-partnair-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo/bxl-cap.png" alt="Logo Bruxelles-Capitale" loading="lazy">
            </li>
            <li class="list-partnair-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo/one.png" alt="Logo ONE" loading="lazy">
            </li>
            <li class="list-partnair-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo/wallonie.png" alt="Logo Wallonie" loading="lazy">
            </li>
            <li class="list-partnair-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo/forem.png" alt="Logo Forem" loading="lazy">
            </li>
        </ul>
        <p>En partenariat avec :</p>
        <ul class="list list-partnair">
            <li class="list-partnair-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo/logo_SEFoP_positif.png" alt="Logo SEFoP" loading="lazy">
            </li>
            <li class="list-partnair-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo/relief.png" alt="Logo Relief" loading="lazy">
            </li>
        </ul>
    </div>

    <svg class="ligne" viewBox="0 0 1280 86" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M1280 5H873.474C857.139 5 841.431 11.7009 829.569 23.7163L796.428 65.2182C786.424 75.362 773.179 81 759.416 81H375.079H57.1737H0" stroke="#FDC224" stroke-width="10" stroke-miterlimit="10" vector-effect="non-scaling-stroke" />
    </svg>

    <div class="wrapper-size">
        <div>
            <h3 class="title title-medium">Contact</h3>
            <ul class="list list-footer">
                <li class="list-item">
                    <p>Du lundi au vendredi de 09h à 18h</p>
                </li>
                <li class="list-item">
                    <p>
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/mail.svg" alt="Icône Email" loading="lazy">
                        infos@fcjmp.be
                    </p>
                </li>
                <li class="list-item">
                    <p>
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/phone.svg" alt="Icône Téléphone" loading="lazy">
                        02 513 64 48
                    </p>
                </li>
                <li class="list-item">
                    <p>
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/maps.svg" alt="Icône Localisation" loading="lazy">
                        Rue Saint Ghislain 26, 1000 Bruxelles
                    </p>
                </li>
            </ul>
        </div>
        <div></div>
        <div>
            <div>
                <form action="https://fcjmp.us13.list-manage.com/subscribe/post?u=2362289d4c5344bda2ad19def&amp;id=0b4fdc2620&amp;f_id=00228aeaf0" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank">
                    <div>
                        <h2 class="title title-medium">Newsletter</h2>
                        <div class="mc-field-group">
                            <label for="mce-EMAIL">Adresse email<span class="asterisk">*</span></label>
                            <input type="email" name="EMAIL" class="required email" id="mce-EMAIL" required value="">
                        </div>
                        <div id="mce-responses" class="clear foot">
                            <div class="response" id="mce-error-response" style="display: none;"></div>
                            <div class="response" id="mce-success-response" style="display: none;"></div>
                        </div>
                        <div aria-hidden="true" style="position: absolute; left: -5000px;">
                            <input type="text" name="b_2362289d4c5344bda2ad19def_0b4fdc2620" tabindex="-1" value="">
                        </div>
                        <div class="optionalParent">
                            <div class="clear foot">
                                <input type="submit" name="subscribe" id="mc-embedded-subscribe" class="button" value="Subscribe">
                                <p style="margin: 0px auto;">
                                    <a href="http://eepurl.com/i4DdCw" title="Avec Mailchimp, les campagnes de marketing par e-mail sont un jeu d'enfant">
                                        <span style="display: inline-block; background-color: transparent; border-radius: 4px;"></span>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <script type="text/javascript" src="//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js"></script>
            <script type="text/javascript">
                (function($) {
                    window.fnames = new Array();
                    window.ftypes = new Array();
                    fnames[0] = 'EMAIL';
                    ftypes[0] = 'email';
                    /*
                     * Translated default messages for the $ validation plugin.
                     * Locale: FR
                     */
                    $.extend($.validator.messages, {
                        required: "Ce champ est requis.",
                        email: "Veuillez entrer une adresse email valide.",
                        maxlength: $.validator.format("Veuillez ne pas entrer plus de {0} caractères."),
                        minlength: $.validator.format("Veuillez entrer au moins {0} caractères.")
                    });
                }(jQuery));
                var $mcj = jQuery.noConflict(true);
            </script>
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>

</html>