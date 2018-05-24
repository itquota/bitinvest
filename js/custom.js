$(document).ready(function() {
$(".isnumber").keydown(function (e) {
      // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
             // Allow: Ctrl+A
            (e.keyCode == 65 && e.ctrlKey === true) ||
             // Allow: Ctrl+C
            (e.keyCode == 67 && e.ctrlKey === true) ||
             // Allow: Ctrl+X
            (e.keyCode == 88 && e.ctrlKey === true) ||
             // Allow: home, end, left, right
            (e.keyCode >= 35 && e.keyCode <= 39)) {
                 // let it happen, don't do anything
                 return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });

  $(".nokey").keydown(function (e) {
       // this.value = this.value.replace(/[^0-9\.]/g,''); 
       $(this).val($(this).val().replace(/[^0-9]/g, ''));

});

});


/*function emailvalidation()
{
	var component = {
		    input   : $('input[name="email"]'),
		    mensage : {
		        fields  : $('.msg'),
		        success : $('.success'),
		        error   : $('.error')
		    }
		},
		    regex  = /^[a-z][a-zA-Z0-9_]*(\.[a-zA-Z][a-zA-Z0-9_]*)?@[a-z][a-zA-Z-0-9]*\.[a-z]+(\.[a-z]+)?$/;

		component.input.blur(function () {
		    component.mensage.fields.hide();
		    regex.test(component.input.val()) ? component.mensage.success.show() : component.mensage.error.show();
		});
}

function mobilevalidation()
{
	var component = {
		    input   : $('input[name="mobile"]'),
		    mensage : {
		        fields  : $('.msg'),
		        success : $('.success'),
		        error   : $('.error_m')
		    }
		},
		    regex  = /^[(]{0,1}[0-9]{3}[)\.\- ]{0,1}[0-9]{3}[\.\- ]{0,1}[0-9]{4}$/;

		component.input.blur(function () {
		    component.mensage.fields.hide();
		    regex.test(component.input.val()) ? component.mensage.success.show() : component.mensage.error.show();
		});
}*/
