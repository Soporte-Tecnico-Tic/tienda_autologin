(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.tienda_autologin = {
    attach: function (context, settings) {
      let $url_host = drupalSettings.tienda_autologin.redirect_host;
      let $url_host_external = drupalSettings.tienda_autologin.redirect_host_external;
      //$url_host_external = encodeURIComponent($url_host_external);
      let $url_redirect = encodeURIComponent(drupalSettings.tienda_autologin.redirect_edituser);

      var params = new window.URLSearchParams(window.location.search);
      if (params.get('mensaje_externo_sync') == 'resetpassword') {
        $("main").once("add-modal-content-pass-user").prepend(`<div class='modal-message-pass-user-form'>
          <div class="modales modal fade" tabindex="-1" id="modal-message-pass-form-auto-login" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <section class="E-espacio-cabecera G-fondo--blanco">
                  <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="margin-right: 60px;"></button>
                  <div class="G-max--700 G-margen--auto" style="padding: 44px 20px 120px">
                    <div id="load-message-user-pass-form-content">
                      <h3 class="G-color--primario G-txt--xxxm G-txt--negrita G-txt--cen">${Drupal.t("La contraseña fue modificada con exito")}</h3>
                      <ul class="G-listado G-flex G-flex-v--cen E-enlaces-adicionales-menu">
                        <li class="enlace-area" style="margin: auto">
                          <a href="/user/login" style="border: 2px solid #296aa5; background-color: #296aa5; color: white; cursor: pointer; text-decoration: none; border-radius: 50px; padding: 7px 14px; line-height: 1;">${Drupal.t("Área usuarios")}</a>
                        </li>
                      </ul>
                    </div>
                  </div>
                </section>
              </div>
            </div>
          </div>
        </div>`);
        $('#modal-message-pass-form-auto-login').show();
        $('#modal-message-pass-form-auto-login').addClass('modal-password-show show');
        
        $("#modal-message-pass-form-auto-login").find('.close').once('modal-load-pass-user-form-auto-login-close').click(function () {
          $('#modal-message-pass-form-auto-login').hide();

          if ($('#modal-message-pass-form-auto-login').hasClass('modal-password-show')) {
            $('#modal-message-pass-form-auto-login').removeClass('modal-password-show show');
          }
        });
      }

      if ($(context).find(".E-enlaces-adicionales-menu .enlace-editar-perfil a").length) {
        //Ocultar el modal
        $("main").once("add-modal-content-edit-user").prepend(`<div class='modal-load-edit-user-form'>
          <div class="modales modal fade" tabindex="-1" id="modal-load-edit-user-form-auto-login" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <section class="E-espacio-cabecera G-fondo--blanco">
                  <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="margin-right: -20px;"></button>
                  <div class="G-max--700 G-margen--auto" style="padding: 44px 20px 120px">
                    <div id="load-edit-user-form-content"></div>
                  </div>
                </section>
              </div>
            </div>
          </div>
        </div>`);


        $(context).find(".E-enlaces-adicionales-menu .enlace-area.enlace-editar-perfil a").once('mostrar-edit-user-page').click(function(event) {
          event.preventDefault();
          $('#modal-load-edit-user-form-auto-login').show();
          $('#modal-load-edit-user-form-auto-login').addClass('modal-edit-user-show show');

          let $url_redirect = encodeURIComponent(drupalSettings.tienda_autologin.redirect_edituser);
          let $url_site = `${$url_host_external}/tienda_autologin/redirect`;

          let height = $(window).height();
          $('#modal-load-edit-user-form-auto-login').find('h3').text("Editar Usuario");
	      $('#modal-load-edit-user-form-auto-login').find("#load-edit-user-form-content").prepend(`<iframe id="iframe_set_password_form" title="Editar usuario" width="580" height="${height}" src="${$url_site}" frameBorder="0"></iframe>`);
        });
        
        $("#modal-load-edit-user-form-auto-login").find('.close').once('modal-load-edit-user-form-auto-login-close').click(function () {        
          $(this).parents('.modal').find('iframe').remove();
          $('#modal-load-edit-user-form-auto-login').hide();

          if ($('#modal-load-edit-user-form-auto-login').hasClass('modal-edit-user-show')) {
            $('#modal-load-edit-user-form-auto-login').removeClass('modal-edit-user-show show');
          }
        });
      }

      //Ocultar el modal
      $("#modal-load-register-form-auto-login .close").click(function () {
        $(this).parents('.modal').find('iframe').remove();
        $('#modal-load-register-form-auto-login').hide();
        
        if ($('#modal-load-register-form-auto-login').hasClass('modal-reset-password-show')) {
          $('#modal-load-register-form-auto-login').removeClass('modal-reset-password-show show');
        }

        if ($('#modal-load-register-form-auto-login').hasClass('modal-register-show')) {
          $('#modal-load-register-form-auto-login').removeClass('modal-register-show show');
        }
      });

      if ($(context).find('a[href*="/usuario/clave"]').length) {
        $(context).find('a[href*="/usuario/clave"]').once('mostrar-reset-password-page').click(function(event) {
          event.preventDefault();

          $('#modal-load-register-form-auto-login').show();
          $('#modal-load-register-form-auto-login').addClass('modal-reset-password-show show');

          let $url_redirect = encodeURIComponent(drupalSettings.tienda_autologin.redirect_resetpassword);
          let $redirect_external = encodeURIComponent(drupalSettings.tienda_autologin.redirect_siteexternal);
          let $url_site = `${$url_host}/redirect/externalsite?destination=${$url_redirect}&redirect_external=${$redirect_external}&autologin=login&op=resetpassword`;

          $('#modal-load-register-form-auto-login').find('h3').text("Recuperar Contraseña");
	      $('#modal-load-register-form-auto-login').find("#load-register-form-content").prepend(`<iframe id="iframe_set_password_form" title="Recuperar contraseña del usuario" width="580" height="450" src="${$url_site}" frameBorder="0"></iframe>`);
        });
      }

      if ($(context).find('a[href*="/usuario/registro"]').length) {
        $(context).find('a[href*="/usuario/registro"]').once('mostrar-register-page').click(function(event) {
          event.preventDefault();
          $('#modal-load-register-form-auto-login').show();
          $('#modal-load-register-form-auto-login').addClass('modal-register-show show');

          let $url_redirect = encodeURIComponent(drupalSettings.tienda_autologin.redirect_register);
          let $url_site = `${$url_host}/redirect/externalsite?redirect=${$url_redirect}&autologin=true&op=register`;

          let height = $(window).height();
	      $('#modal-load-register-form-auto-login').find("#load-register-form-content").prepend(`<iframe id="iframe_register_form" title="Registro de usuario" width="580" height="${height}" src="${$url_site}" frameBorder="0"></iframe>`);

        });
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
