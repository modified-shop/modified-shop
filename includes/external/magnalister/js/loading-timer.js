(function($){
	var isSafari = /^((?!chrome).)*safari/i.test(navigator.userAgent);
	//isSafari = true;
	//console.log('isSafari', isSafari);
	$(document).ready(function() {
		$(".magnamain :submit").click(function (e) {
			if (isSafari) { // Normally you'd expect IE here, but this time Safai is like WTF!
				//console.log('Safari');
				e.preventDefault();
				var tehForm = $(this).parents('form'),
					btnName = $(this).attr('name') || '';
				// Pass the information which button has been pressed. For some forms it is important
				if (btnName != '') {
					tehForm.append($('<input>').attr({
						'type': 'hidden',
						'name': btnName,
						'value': $(this).attr('value') || ''
					}));
				}
				$.blockUI(jQuery.extend(blockUILoading, {
					onBlock: function () {
						//console.log('Submit');
						tehForm.submit();
					}
				}));
				return false;
			} else {
				setTimeout(function() { $.blockUI(blockUILoading); }, 1000);
				return true;
			}
		});
		$('.magnaTabs2 a').on("click", "a:not(.breadcrumb)", function (e) {
			if ($(this).attr('target') != '_blank') {
				if (isSafari) { // Same here.
					//console.log('Safari');
					e.preventDefault();
					var sHref = $(this).attr('href');
					$.blockUI(jQuery.extend(blockUILoading, {
						onBlock: function () {
							//console.log('Link');
							document.location.href = sHref;
						}
					}));
					return false;
				} else {
					setTimeout(function() { $.blockUI(blockUILoading); }, 1000);
					return true;
				}
			}
		});
		$(".magnamain .productList select").change(function () {
			if (   !$(this).hasClass('ml-js-noBlockUi')
				&& !$(this).parents().hasClass('config') 
				&& !$(this).is('#marketplacesync') 
				&& !$(this).parents().hasClass('attributesTable')
			) {
				setTimeout(function(){$.blockUI(blockUILoading);}, 1000);
			}
		});
	});
})(jQuery);
