/**
 * @file
 * Attaches behaviors for the D324 Ace module.
 */

(function($, Drupal, drupalSettings) {

  "use strict";

  var drake;

  Drupal.behaviors.d324_ace_frontend = {
    attach: function(context, settings) {
      var cut_links = $('.d324-ace-frontend-paragraph-links', context).find('.d324-ace-button-cut');
      cut_links.on('click', function (e) {
        e.preventDefault();

        var $this = $(e.currentTarget);

        // Find parent paragraph.
        var parent = $this.closest('[data-ace-paragraph-id]');
        var parent_id = parent.data('ace-paragraph-id');
        parent.addClass('d324-ace-cut-paste-disabled');

        // Find the ace field wrapper.
        var field_wrapper_id = $this.data('ace-field-wrapper');
        var field_wrapper = $('[data-ace-field-wrapper="' + field_wrapper_id + '"]', context);
        // Add class to the ace field wrapper to toggle cut behavior.
        field_wrapper.addClass('d324-ace-cut-paste');

        // Rewrite all paste links based on the paragraph which is currently cut.
        var paragraphs = $('[data-ace-paragraph-id]', field_wrapper);
        paragraphs.each(function(index, paragraph) {
          paragraph = $(paragraph);
          var paragraph_id = paragraph.data('ace-paragraph-id');
          var paste_links = $('.d324-ace-button-paste_before, .d324-ace-button-paste_after', paragraph);
          paste_links.each(function(index, paste_link) {
            paste_link = $(paste_link);
            var href = paste_link.attr('href');

            $.each(Drupal.ajax.instances, function (index, event) {
              var element = $(event.element);
              if (element.hasClass('d324-ace-paste')) {
                if (href === event.element_settings.url) {
                  event.options.url = event.options.url.replace('/' + paragraph_id + '/', '/' + parent_id + '/');
                }
              }
            });
          });
        });

        return false;
      });
    }
  };

  Drupal.AjaxCommands.prototype.aceReattachBehaviors = () => {
    Drupal.ajax.instances = Drupal.ajax.instances.filter(el => {
      return el != null;
    });

    Drupal.attachBehaviors();
    Drupal.blazy.init.revalidate();
  };

  Drupal.AjaxCommands.prototype.aceScrollToParagraph = function (ajax, response, status) {
    var paragraph_id = response.paragraph_id;
    $(document).scrollTop($('div[data-ace-paragraph-id="' + paragraph_id + '"]').first().offset().top - 100);
  };

})(jQuery, Drupal, drupalSettings);
