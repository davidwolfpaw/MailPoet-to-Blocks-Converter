window.MailPoet_to_Blocks_Admin = window.MailPoet_to_Blocks_Admin || {};

(function (window, document, $, app) {
  "use strict";

  app.l10n = window.mailpoet_to_blocks_admin_config || {};

  app.init = function () {
    app.$ = {};

    app.$.convert_newsletters_button = $(
      document.getElementById("convert-newsletters")
    );
    app.$.convert_newsletters_log = $(
      document.getElementById("convert-newsletters-log")
    );

    app.$.convert_newsletters_button.on("click", app.convert_newsletters);
  };

  app.convert_newsletters = function (event) {
    event.preventDefault();

    app.$.convert_newsletters_log.append("<p>" + app.l10n.converting + "</p>");

    app.do_ajax_request(
      "convert_newsletters_to_blocks",
      app.l10n.convert_nonce
    );
  };

  app.do_ajax_request = function (action, nonce) {
    var data = {
      action: action,
      convert_nonce: nonce,
    };

    // submit the form via ajax.
    $.ajax({
      url: ajaxurl,
      type: "POST",
      dataType: "json",
      data: data,
    }).done(function (response) {
      // bail early if not successful.
      if (true !== response.success) {
        alert(app.l10n.convert_error);
        return false;
      }
      // append response to log.
      app.$.convert_newsletters_log.html(response);
    });
  };

  $(document).ready(app.init);

  return app;
})(window, document, jQuery, window.MailPoet_to_Blocks_Admin);
