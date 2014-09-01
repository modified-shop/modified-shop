/* Fix the Jquery serialize so that it works with ISO-8859-1 */
jQuery.fn.extend({
    param: function( a ) {
        var s = [];
 
        // If an array was passed in, assume that it is an array
        // of form elements
        if ( a.constructor == Array || a.jquery ){
            // Serialize the form elements
            jQuery.each( a, function(){
                s.push(unescape(encodeURIComponent(escape(this.name))) + "=" + unescape(encodeURIComponent(escape(this.value))));
            });
        }
        // Otherwise, assume that it's an object of key/value pairs
        else{
            // Serialize the key/values
            for ( var j in a )
                // If the value is an array then the key names need to be repeated
                if ( a[j] && a[j].constructor == Array )
                    jQuery.each( a[j], function(){
                        s.push(unescape(encodeURIComponent(escape(j)) + "=" + encodeURIComponent(escape(this))));
                    });
                else
                    s.push(unescape(encodeURIComponent(escape(j)) + "=" + encodeURIComponent(escape(a[j]))));
        }
        // Return the resulting serialization
        return s.join("&").replace(/ /g, "+");
    },

    serialize: function() {
        return this.param(this.serializeArray());
    }
   

});  

$(document).ready(function(){ 
     $("#add-invoice-form").submit(function(){ 
         $.post( 
             "add_transaction.php", 
             $("#add-invoice-form").serialize(), 
             function(data){ 
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             } 
         ); 
     }); 
});  

$(document).ready(function(){ 
     $("#get-address-form").submit(function(){ 
         $.post( 
             "get_addresses.php", 
             $("#get-address-form").serialize(), 
             function(data){ 
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             } 
         ); 
     }); 
});  

$(document).ready(function(){ 
     $("#monthly-cost-form").submit(function(){ 
         $.post( 
             "monthly_cost.php", 
             $("#monthly-cost-form").serialize(), 
             function(data){ 
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             } 
         ); 
     }); 
});  

$(document).ready(function(){ 
     $("#periodic-cost-form").submit(function(){ 
         $.post( 
             "periodic_cost.php", 
             $("#periodic-cost-form").serialize(), 
             function(data){ 
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             } 
         ); 
     }); 
}); 

$(document).ready(function(){ 
     $("#activate-invoice-form").submit(function(){ 
     	if(verify_invno($('#activate-invoice-form input[name$="invno"]').val()))
     	{
         $.post( 
             "activate_invoice.php", 
             $("#activate-invoice-form").serialize(), 
             function(data){ 
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             } 
         ); 
         }
     }); 
}); 

$(document).ready(function(){ 
     $("#delete-invoice-form").submit(function(){ 
     	if(verify_invno($('#delete-invoice-form input[name$="invno"]').val()))
     	{
         $.post( 
             "delete_invoice.php", 
             $("#delete-invoice-form").serialize(), 
             function(data){ 
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             } 
         ); 
         }
     }); 
}); 

$(document).ready(function(){ 
     $("#return-amount-form").submit(function(){ 
     	if(verify_invno($('#return-amount-form input[name$="invno"]').val()))
     	{
         $.post( 
             "return_amount.php", 
             $("#return-amount-form").serialize(), 
             function(data){ 
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             } 
         ); 
         }
     }); 
}); 

$(document).ready(function(){ 
     $("#credit-invoice-form").submit(function(){ 
     	if(verify_invno($('#credit-invoice-form input[name$="invno"]').val()))
     	{
         $.post( 
             "credit_invoice.php", 
             $("#credit-invoice-form").serialize(), 
             function(data){ 
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             } 
         ); 
         }
     }); 
});  

$(document).ready(function(){ 
     $("#credit-part-form").submit(function(){ 
     	if(verify_invno($('#credit-part-form input[name$="invno"]').val()))
     	{
         $.post( 
             "credit_part.php", 
             $("#credit-part-form").serialize(), 
             function(data){ 
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             } 
         ); 
         }
     }); 
});  

$(document).ready(function(){ 
     $("#email-invoice-form").submit(function(){ 
     	if(verify_invno($('#email-invoice-form input[name$="invno"]').val()))
     	{
         $.post( 
             "email_invoice.php", 
             $("#email-invoice-form").serialize(), 
             function(data){ 
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             } 
         ); 
         }
     }); 
});  

$(document).ready(function(){ 
     $("#send-invoice-form").submit(function(){ 
     	if(verify_invno($('#send-invoice-form input[name$="invno"]').val()))
     	{
         $.post( 
             "send_invoice.php", 
             $("#send-invoice-form").serialize(), 
             function(data){ 
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             } 
         ); 
         }
     }); 
}); 

$(document).ready(function(){ 
     $("#update-goods-qty-form").submit(function(){ 
     	if(verify_invno($('#update-goods-qty-form input[name$="invno"]').val()))
     	{
         $.post( 
             "update_goods_qty.php", 
             $("#update-goods-qty-form").serialize(), 
             function(data){ 
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             } 
         ); 
         }
     }); 
}); 

$(document).ready(function(){ 
     $("#update-charge-form").submit(function(){ 
     	if(verify_invno($('#update-charge-form input[name$="invno"]').val()))
     	{
         $.post( 
             "update_charge.php", 
             $("#update-charge-form").serialize(), 
             function(data){ 
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             } 
         ); 
         }
     }); 
}); 

$(document).ready(function(){ 
     $("#update-orderno-form").submit(function(){ 
     	if(verify_invno($('#update-orderno-form input[name$="invno"]').val()))
     	{
         $.post( 
             "update_orderno.php", 
             $("#update-orderno-form").serialize(), 
             function(data){ 
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             } 
         ); 
         }
     }); 
}); 

$(document).ready(function(){ 
     $("#invoice-addr-form").submit(function(){ 
     	if(verify_invno($('#invoice-addr-form input[name$="invno"]').val()))
     	{
         $.post( 
             "invoice_addr.php", 
             $("#invoice-addr-form").serialize(), 
             function(data){ 
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             } 
         ); 
         }
     }); 
}); 

$(document).ready(function(){ 
     $("#invoice-amount-form").submit(function(){ 
     	if(verify_invno($('#invoice-amount-form input[name$="invno"]').val()))
     	{
         $.post( 
             "invoice_amount.php", 
             $("#invoice-amount-form").serialize(), 
             function(data){ 
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             } 
         ); 
         }
     }); 
}); 

$(document).ready(function(){ 
     $("#invoice-part-amount-form").submit(function(){ 
     	if(verify_invno($('#invoice-part-amount-form input[name$="invno"]').val()))
     	{
         $.post( 
             "invoice_part_amount.php", 
             $("#invoice-part-amount-form").serialize(), 
             function(data){ 
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             } 
         ); 
         }
     }); 
});  

$(document).ready(function(){ 
     $("#get-pclasses-form").submit(function(){ 
         $.post( 
             "fetch_pclasses.php", 
             $("#get-pclasses-form").serialize(), 
             function(data){ 
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             } 
         ); 
     }); 
}); 

$(document).ready(function(){ 
     $("#is-invoice-paid-form").submit(function(){ 
         $.post( 
             "is_invoice_paid.php", 
             $("#is-invoice-paid-form").serialize(), 
             function(data){ 
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             } 
         ); 
     }); 
}); 

$(document).ready(function(){ 
     $("#reserve-amount-form").submit(function(){ 
         $.post( 
             "reserve_amount.php", 
             $("#reserve-amount-form").serialize(), 
             function(data){ 
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             } 
         ); 
     }); 
}); 

$(document).ready(function(){ 
     $("#activate-reservation-form").submit(function(){ 
         $.post( 
             "activate_reservation.php", 
             $("#activate-reservation-form").serialize(), 
             function(data){ 
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             } 
         ); 
     }); 
}); 

$(document).ready(function(){ 
     $("#cancel-reservation-form").submit(function(){ 
         $.post( 
             "cancel_reservation.php", 
             $("#cancel-reservation-form").serialize(), 
             function(data){
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             }
         ); 
     }); 
}); 

$(document).ready(function(){ 
     $("#split-reservation-form").submit(function(){ 
         $.post( 
             "split_reservation.php", 
             $("#split-reservation-form").serialize(), 
             function(data){
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             }
         ); 
     }); 
}); 

$(document).ready(function(){ 
     $("#change-reservation-form").submit(function(){ 
         $.post( 
             "change_reservation.php", 
             $("#change-reservation-form").serialize(), 
             function(data){
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             }
         ); 
     }); 
}); 

$(document).ready(function(){ 
     $("#reserve-ocrs-form").submit(function(){ 
         $.post( 
             "reserve_ocrs.php", 
             $("#reserve-ocrs-form").serialize(), 
             function(data){
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             }
         ); 
     }); 
}); 

$(document).ready(function(){ 
     $("#estore-info").submit(function(){ 
         $.post( 
             "estore_set.php", 
             $("#estore-info").serialize(), 
             function(data){
             	$("#result-dialog").html(data);
                $("#result-dialog").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
             }
         ); 
     }); 
}); 
