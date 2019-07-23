(function ($, Drupal, drupalSettings) {

  'use strict';

  var drake;

  Drupal.behaviors.fieldUID324Ace = {

    attach: function attach(context, settings) {

      var updateDisabled = function($container) {
        if ($container.find('.ace-layout .ace-field-item').length > 0) {
          $container.find('.ace-layout-disabled').show();
        }
        else {
          $container.find('.ace-layout-disabled').hide();
        }
        if ($container.find('.ace-layout-disabled .ace-field-item').length > 0) {
          $container.find('.ace-layout-disabled-description').hide();
        }
        else {
          $container.find('.ace-layout-disabled-description').show();
        }
      };

      var updateFields = function($container) {

        // Set deltas:
        $container.find('.ace-field-item').each(function(index, item){
          $(item).find('.ief-entity-delta').val(index + '');
        });

        // Set parents:
        $container.parent().find('.ace-field-item .parent-delta').each(function(index, item){
          var d = getParentDelta($(item));
          if (d >= 0) {
            $(item).val(d);
          }
        });

        // Set regions:
        $container.find('.ace-field-item .ace-region-select').each(function(index, item){
          var $el = $(item);
          if ($el.parents('.ace-region-section')) {
            $el.val(getRegion($el));
          }
        });
      };

      var moveUp = function($item, $container) {
        if ($item.parents('.ace-layout-section').length == 0) {
          $item = $item.parent();
        }
        if ($item.prev().length > 0) {
          $item.after($item.prev());
          updateFields($container);
        }
      };

      var moveDown = function($item, $container) {
        if ($item.parents('.ace-layout-section').length == 0) {
          $item = $item.parent();
        }
        if ($item.next().length > 0) {
          $item.before($item.next());
          updateFields($container);
        }
      };

      var addLayoutControls = function($container) {
        $container.find('.ace-field-item').each(function(index, fieldItem){
          var $fieldItem = $(fieldItem);
          $fieldItem
            .remove('.layout-controls')
            .append($('<div class="layout-controls">')
              .append($('<div class="layout-up"></div>').mousedown(
                function(){
                  moveUp($fieldItem, $container);
                }
              ))
              .append($('<div class="layout-handle"></div>'))
              .append($('<div class="layout-down"></div>').mousedown(
                function(){
                  moveDown($fieldItem, $container);
                }
              )
            )
          );
        });
      };

      var getRegion = function($el) {
        var regEx = /ace-layout-section--([a-z0-9A-Z_]*)/,
          regionName = '',
          $container = $el.is('.ace-layout-section') ? $el : $el.parents('.ace-layout-section');
        if ($container.length) {
          var matches = $container[0].className.match(regEx);
          if (matches && matches.length >= 2) {
            regionName = matches[1];
          }
        }
        return regionName;
      };

      var getParentDelta = function($el) {
        var regEx = /ace-layout-delta--([0-9]+)/,
        delta = -1,
        $container = $el.is('.ace-layout-section') ? $el : $el.parents('.ace-layout-section');
        // Has a section parent
        if ($container.length) {
          var matches = $container[0].className.match(regEx);
          if (matches && matches.length >= 2) {
            delta = matches[1];
          }
        }
        return delta;
      };

      var getSiblingDelta = function($el) {
        var regEx = /ace-layout-delta--([0-9]+)/,
        delta = -1,
        $container = $el.is('.ace-field-item--layout') ? $el : $el.parents('.ace-field-item--layout:first');
        if ($container.length) {
          delta = $container.prev('.ace-field-item--layout').find('> .ace-field-item .ief-entity-delta').val()
        }
        return delta;
      };

      var addNewButton = function($buttonGroup, $optionItem, $section, $aceField, prefix) {
        prefix = prefix ? prefix : '';
        var icon = '';
        if (drupalSettings.aceIcons && drupalSettings.aceIcons['icon_' + $optionItem.val()]) {
          icon = '<img src="' + drupalSettings.aceIcons['icon_' + $optionItem.val()] + '" />';
        }

        $buttonGroup.append($('<button>' + icon + prefix + $optionItem.text() + '</button>')
        .click(function(e){
          return false;
        })
        .mousedown(function(e){
          $aceField.find('.ace-new-item-region').val(getRegion($section));
          $aceField.find('.ace-field-actions select').val($optionItem.val());
          var parent = getParentDelta($section);
          if (parent < 0 ) {
            parent = getSiblingDelta($section);
          }
          $aceField.find('.ace-new-item-delta').val(parent);
          $aceField.find('.ace-field-actions input.js-form-submit').trigger('mousedown');
          return false;
        }));
      };

      var buttonGroup = function($types, $section, $aceField) {
        var $addButtons = $('<div class="ace-add-content--group hidden"></div>');
        $types.each(function(index, elem) {
          addNewButton($addButtons, $(elem), $section, $aceField);
        });
        var $addContent = $('<button class="ace-add-content">+</button>')
          .appendTo($section)
          .on('click', function(e){
            $(e.target).focus();
            return false;
          })
          .on('click', function(e){
            var $b = $(e.target);
            $b.parent().find('.ace-add-content--group').toggleClass('hidden');
            $b.toggleClass('active');
            $b.text($b.text() == '+' ? '-' : '+');
            return false;
          });
        $(window).click(function(){
          $aceField.find('.ace-add-content--group').addClass('hidden');
          $aceField.find('.ace-add-content').removeClass('active');
        });
        $section.append($addButtons);
      };

      var addRegionButtons = function($aceField) {
        var $types = $aceField.find('.ace-field-actions optgroup[label="Content"] option');
        $aceField.find('.ace-layout-section').each(function(index, section) {
          if ($(section).parents('.ace-layout-disabled').length == 0) {
            buttonGroup($types, $(section), $aceField);
          }
        });
      };

      var addSectionButtons = function($aceField) {
        var $types = $aceField.find('.ace-field-actions optgroup[label="Layout"] option');
        $aceField.parent().find('.ace-field-actions:first').each(function(index, section) {
          if ($types.length > 1) {
            buttonGroup($types, $(section), $aceField);
          }
          else {
            // Create the "Add section" button above disabled area.
            var $addSection = $('<div class="ace-add-content--single"></div>').insertBefore($aceField.find('.ace-layout-disabled'));
            addNewButton($addSection, $types, $(section), $aceField, '<span class="icon">+</span> Add ');

            // Add it below all other sections except the first one.
            $aceField.find('.ace-field-item--layout > .ace-field-item').each(function(index, item){
              var $addSection = $('<div class="ace-add-content--single"></div>').appendTo($(item));
              addNewButton($addSection, $types, $(item), $aceField, '<span class="icon">+</span> Add ');
            });
          }
        });
      };

      var enhanceRadioSelect = function() {
        $('.layout-radio-item').click(function(){
            $(this).find('input[type=radio]').prop("checked", true).trigger("change");
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
        });
        $('.layout-radio-item').each(function(){
          if ($(this).find('input[type=radio]').prop("checked")) {
            $(this).addClass('active');
          }
        });
      };

      var editableLayout = function($elem, options) {
        var forceLayouts = options.forceLayouts ? true : false;
        updateFields($elem);
        updateDisabled($elem);

        // Turn on drag and drop if dragula function exists.
        if (typeof dragula !== 'undefined') {
          $elem.addClass('dragula-enabled');
          if (drake) {
            drake.destroy();
          }
          drake = dragula([].slice.apply(document.querySelectorAll('.ace-layout-section, .ace-field-item--layout-container')), {
            moves: function(el, container, handle) {
              return handle.className.toString().indexOf('layout-handle') >= 0;
              },

            accepts: function(el, target, source, sibling) {
              // Layouts can never inside another layout.
              if ($(el).is('.ace-field-item--layout')) {
                if ($(target).parents('.ace-field-item--layout').length) {
                  return false;
                }
              }

              // Layouts can not be dropped into disabled (only individual items).
              if ($(el).is('.ace-field-item--layout')) {
                if ($(target).parents('.ace-layout-disabled').length) {
                  return false;
                }
              }

              // Require non-layout items to be dropped in a layout.
              if ($(el).is('.ace-field-item')) {
                if($(target).parents('.ace-field-item--layout').length == 0 && $(target).parents('.ace-layout-disabled').length == 0) {
                  return false;
                }
              }

              return true;
            }
          });
          drake.on('drop', function(el, target, source, sibling){
            updateFields($elem);
            updateDisabled($elem);
          });

        }
        addRegionButtons($elem);
        addSectionButtons($elem);
        addLayoutControls($elem);

      };

      $('.ace-field', context).once('aceBehaviors').each(function(index, item){
        $('#ace-modal').dialog(
          {
            width: 1000,
            appendTo: $('#ace-modal').parents('.ace-field'),
            close: function (event, ui){
              $('#ace-modal').find('input[value="Cancel"]').trigger('mousedown');
            },
            open: function (event, ui) {
              enhanceRadioSelect();
            },
            modal: true,
            title: $('#ace-modal .ief-title').text()
          }
        );
        editableLayout($(item), {forceLayouts: $(item).hasClass('ace-field-force-layouts')});
      });

    }
  };

})(jQuery, Drupal, drupalSettings);
