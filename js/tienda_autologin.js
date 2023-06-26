(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.tienda_autologin = {
    attach: function (context, settings) {
      let $url_host = drupalSettings.tienda_autologin.redirect_host;
      let $uid = drupalSettings.tienda_autologin.user_external;

      if ($(context).find(".E-enlaces-adicionales-menu .enlace-area a").length && (uid > 0)) {
        //Ocultar el modal
        $(".modal-load-edit-user-form .close").click(function () {
          $(this).parents('.modal').find('iframe').remove();
          $('.modal-load-edit-user-form-auto-login').hide();
        
          if ($('.modal-load-edit-user-form').hasClass('modal-edit-user-show')) {
            $('.modal-load-edit-user-form').removeClass('modal-edit-user-show');
          }
        });

          $("footer .container").once("add-modal-content-edit-user").prepend(`<div class='modal-load-edit-user-form'>
            <div class="modales modal fade" tabindex="-1" id="modal-load-edit-user-form-auto-login" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <section class="E-espacio-cabecera G-fondo--blanco">
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="float:right; padding-right: 25px; margin-top: -10px; box-shadow: none;"></button>
                    <div class="G-max--700 G-margen--auto" style="padding: 44px 20px 120px">
                      <h3 style="padding-left: 44px; padding-right: 44px; font-weight: bold" class="G-txt--mayus G-txt--cen G-color--primario G-txt--xl G-margen--xxm G-margen--lr-0 G-margen--top-0">Editar usuario</h3>
                      <div id="load-edit-user-form-content"></div>
                    </div>
                  </section>
                </div>
              </div>
            </div>
          </div>`);

        $(context).find(".E-enlaces-adicionales-menu .enlace-area a").once('mostrar-edit-user-page').click(function(event) {
          event.preventDefault();

          $('#modal-load-edit-user-form-auto-login').show();
          $('#modal-load-edit-user-form-auto-login').addClass('modal-edit-user-show');

          let $url_redirect = encodeURIComponent(drupalSettings.tienda_autologin.redirect_edituser);
          let $url_site = `${$url_host}/user/${$uid}/edit?redirect=${$url_redirect}&autologin=true&op=edituser`;
          
          let height = $(window).height() - 100;
          $('#modal-load-edit-user-form-auto-login').find('h3').text("Editar Usuario");
	      $('#modal-load-edit-user-form-auto-login').find("#load-edit-user-form-content").prepend(`<iframe id="iframe_set_password_form" title="Editar usuario" width="580" height="${height}" src="${$url_site}" frameBorder="0"></iframe>`);
        });
      }

      //Ocultar el modal
      $("#modal-load-register-form-auto-login .close").click(function () {
        $(this).parents('.modal').find('iframe').remove();
        $('#modal-load-register-form-auto-login').hide();
        
        if ($('#modal-load-register-form-auto-login').hasClass('modal-reset-password-show')) {
          $('#modal-load-register-form-auto-login').removeClass('modal-reset-password-show');
        }

        if ($('#modal-load-register-form-auto-login').hasClass('modal-register-show')) {
          $('#modal-load-register-form-auto-login').removeClass('modal-register-show');
        }
      });

      if ($(context).find('a[href*="/usuario/clave"]').length) {
        $(context).find('a[href*="/usuario/clave"]').once('mostrar-reset-password-page').click(function(event) {
          event.preventDefault();

          $('#modal-load-register-form-auto-login').show();
          $('#modal-load-register-form-auto-login').addClass('modal-reset-password-show');

          let $url_redirect = encodeURIComponent(drupalSettings.tienda_autologin.redirect_resetpassword);
          let $url_site = `${$url_host}/user/password?redirect=${$url_redirect}&autologin=true&op=resetpassword`;

          $('#modal-load-register-form-auto-login').find('h3').text("Recuperar Contraseña");
	      $('#modal-load-register-form-auto-login').find("#load-register-form-content").prepend(`<iframe id="iframe_set_password_form" title="Recuperar contraseña del usuario" width="580" height="450" src="${$url_site}" frameBorder="0"></iframe>`);
        });
      }

      if ($(context).find('a[href*="/usuario/registro"]').length) {
        $(context).find('a[href*="/usuario/registro"]').once('mostrar-register-page').click(function(event) {
          event.preventDefault();
          $('#modal-load-register-form-auto-login').show();
          $('#modal-load-register-form-auto-login').addClass('modal-register-show');

          let $url_redirect = encodeURIComponent(drupalSettings.tienda_autologin.redirect_register);
          let $url_site = `${$url_host}/user/register?redirect=${$url_redirect}&autologin=true&op=register`;

          let height = $(window).height() - 300;
	      $('#modal-load-register-form-auto-login').find("#load-register-form-content").prepend(`<iframe id="iframe_register_form" title="Registro de usuario" width="580" height="${height}" src="${$url_site}" frameBorder="0"></iframe>`);

        });
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
