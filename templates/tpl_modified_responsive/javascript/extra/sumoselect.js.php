<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2019 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
?>
<script>
  // handlers on the select itself, e.g. the change handler of native selects on mobile devices
  function sumoHandlers(select) {
    var handlers = [];
    $.each($._data(select, 'events') || {}, function(type, list) {
      handlers = handlers.concat(list);
    });
    return handlers;
  }

  function initSumoSelect(root) {
    // the root itself counts too, e.g. a select with new options
    var elements = function(selector) {
      return $(root).find(selector).addBack(selector);
    };
    // unload() keeps the handlers that a start binds to the select, each start remembers its own
    var selects = elements('select').filter(function() {
      return !this.sumo;
    }).get();
    var before = selects.map(sumoHandlers);
    <?php if (defined('ADVANCED_SUMOSELECT_SEARCHFIELD') && ADVANCED_SUMOSELECT_SEARCHFIELD == true) { ?>
      elements('select:not([name=filter_sort]):not([name=filter_set]):not([name=currency]):not([name=categories_id]):not([name=gender]):not([id^=sel_]):not([id=ec_term])').SumoSelect({search: true, searchText: "<?php echo TEXT_SUMOSELECT_SEARCH; ?>", noMatch: "<?php echo TEXT_SUMOSELECT_NO_RESULT; ?>"});
      elements('select[name=filter_sort]').SumoSelect();
      elements('select[name=filter_set]').SumoSelect();
      elements('select[name=currency]').SumoSelect();
      elements('select[name=categories_id]').SumoSelect();
      elements('select[name=gender]').SumoSelect();
      elements('select[id^=sel_]').SumoSelect();
      elements('select[id=ec_term]').SumoSelect();
    <?php } else { ?>
      elements('select:not([name=country])').SumoSelect();
      elements('select[name=country]').SumoSelect({search: true, searchText: "<?php echo TEXT_SUMOSELECT_SEARCH; ?>", noMatch: "<?php echo TEXT_SUMOSELECT_NO_RESULT; ?>"});
    <?php } ?>
    selects.forEach(function(select, i) {
      if (select.sumo) {
        $(select).data('sumoHandlers', sumoHandlers(select).filter(function(handler) {
          return before[i].indexOf(handler) < 0;
        }));
      }
    });
  }

  // content that a script has inserted into the page
  document.addEventListener('modified:content-updated', function(event) {
    initSumoSelect(event.detail.root);
  });

  // content that a script is about to replace or remove
  document.addEventListener('modified:before-content-update', function(event) {
    var root = event.detail.root;
    var selects = $(root).find('select').addBack('select').filter(function() {
      return this.sumo;
    });
    if (selects.length) {
      selects.each(function() {
        var select = this;
        var wrapper = select.sumo.select[0];
        // an open list keeps the scroll lock of the body and its document click handler
        select.sumo.hideOpts();
        select.sumo.unload();
        $.each($(select).data('sumoHandlers') || [], function(i, handler) {
          $(select).off(handler.origType, handler.handler);
        });
        $(select).removeData('sumoHandlers');
        // unload() removes the wrapper, as root it stays in the page as a plain container of the select
        if (wrapper === root) {
          while (root.attributes.length) {
            root.removeAttribute(root.attributes[0].name);
          }
          $(root).empty().insertBefore(select).append(select);
        }
      });
      // unload() keeps the resize handler of each instance, so the remaining ones bind it again
      $(window).off('resize.sumo');
      $('select').each(function() {
        var sumo = this.sumo;
        // native selects on mobile devices have no list and no resize handler
        if (sumo && !sumo.mob) {
          $(window).on('resize.sumo', function() {
            sumo.floatingList();
          });
        }
      });
    }
  });

  $(document).ready(function() {

    initSumoSelect(document);

    var selectWord = '';
    var selectTimer = null;
    $('body').on('keydown', function(e){
        var target = $(e.target);
        var tmpClass = target.attr("class");
        if(typeof(tmpClass) != "undefined"){
            if(tmpClass.indexOf("SumoSelect") > -1){
                var char = String.fromCharCode(e.keyCode);
                if(char.match('\d*\w*')){
                    selectWord += char;
                }
                clearTimeout(selectTimer); //cancel the previous timer.
                selectTimer = null;
                selectTimer = setTimeout(function(){
                    var select = target.find("select");
                    var options = target.find("select option");
                    for(var x = 0; x < options.length; x++){
                        var option = options[x];
                        var optionText = option.text.toLowerCase();
                        if(optionText.indexOf(selectWord.toLowerCase()) == 0){
                            var ul = target.find("ul");
                            var li = target.find(".selected");
                            var offsetUl = ul.offset();
                            var offsetLi = li.offset();
                            console.log(option.text);
                            select.val(option.value);
                            select.trigger("change");
                            select[0].sumo.unSelectAll();
                            select[0].sumo.toggSel(true,option.value);
                            select[0].sumo.reload();
                            select[0].sumo.setOnOpen();
                            newLi = $(select[0].sumo.ul).find(".selected");
                            var offsetNewLi = newLi.offset();
                            ul = select[0].sumo.ul;
                            var newOffset = offsetNewLi.top - offsetUl.top;
                            ul.scrollTop(0);
                            ul.scrollTop(newOffset);
                            console.log(offsetUl.top +"~"+offsetLi.top+"~"+offsetNewLi.top);
                            break;
                        }
                    }
                    selectWord = '';
                }, 500);
            }
        }
    });

    /* Mark Selected */
    var tmpStr = '';
    $('.filter_bar .SumoSelect').each(function(index){
      ($(this).find('select').val() == '') ? $(this).find('p').removeClass("Selected") : $(this).find('p').addClass("Selected");
    });
  });
</script>