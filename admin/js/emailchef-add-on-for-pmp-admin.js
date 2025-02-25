(function ($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

    $(document).ready(function () {

        var form = $("#ecf-login-form");
        var formSubmit = $("#ecf-login-submit");
        var showPassword = $("#showPassword");
        var hidePassword = $("#hidePassword");

        $(showPassword).on("click", function () {
            $('input[name="pmproecaddon_consumer_secret').attr("type", "text");
            $(showPassword).hide();
            $(hidePassword).show();
        });

        $(hidePassword).on("click", function () {
            $('input[name="pmproecaddon_consumer_secret').attr("type", "password");
            $(hidePassword).hide();
            $(showPassword).show();
        });

        $(formSubmit).on("click", function () {
            $('.emailchef-check-login-result').removeClass('notice-error notice-success').hide();
            $('#ecf-login-submit').prop('disabled', true);

            jQuery.post(ajaxurl, {
                'action': 'emailchef-add-on-for-pmp_check_login',
                'consumer_key': $('input[name="pmproecaddon_consumer_key').val(),
                'consumer_secret': $('input[name="pmproecaddon_consumer_secret').val(),
                '_pmproecaddon_nonce': $('input[name="_pmproecaddon_nonce"]').val()
            }, function (res) {
                if (res && res.success) {
                    $('.emailchef-check-login-result').addClass('notice-success').html('<p>' + emailchefPMPI18n.login_correct + '</p>').show();
                    $('input[name="pmproecaddon_consumer_key"],input[name="pmproecaddon_consumer_secret').addClass('valid');
                    window.location.reload();
                } else {
                    $('.emailchef-check-login-result').addClass('notice-error').html('<p>' + emailchefPMPI18n.login_failed + '</p>').show();
                    $('input[name="pmproecaddon_consumer_key"],input[name="pmproecaddon_consumer_secret').addClass('error');
                    $('#ecf-login-submit').prop('disabled', false);
                }
            });

        });

        $("#emailchef-disconnect").on("click", function () {

            if (confirm(emailchefPMPI18n.disconnect_account_confirm)) {

                var data = {
                    'action': 'emailchef-add-on-for-pmp_disconnect',
                    '_pmproecaddon_nonce': $("#emailchef-disconnect").data("nonce")
                };

                jQuery.post(ajaxurl, data, function (response) {
                    if (response.success) {
                        window.reload();
                    }
                });

            }

        });


    });


})(jQuery);
