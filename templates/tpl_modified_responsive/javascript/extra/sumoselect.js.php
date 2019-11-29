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
  $(document).ready(function() {
    $('select').SumoSelect();
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