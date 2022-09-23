jQuery(function( $ ) {
	'use strict';

	$(document).on("click", "#import_pullgames", function(){
		$.ajax({
			type: "post",
			url: pullgames.ajaxurl,
			data: {
				action: "pullgames_imports"
			},
			dataType: "json",
			beforeSend: ()=>{
				$("#loader-1").show()
				$("#import_pullgames").prop("disabled", true)
			},
			success: function (response) {
				$("#loader-1").hide();
				if(response.success){
					location.reload();
				}
			}
		});
	});

});
