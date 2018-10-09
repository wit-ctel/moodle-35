$( document ).ready(function() {
	$(".action").click(function(){
		var UID = $(this).attr('id');
		var moduleID = UID.substring(3);
		var div = $(this);
		if (div.html()=="Import Module Content"){
			var url = "requestimport.php?moduleid="+moduleID;
		}
		else{
			var url = "cancelimport.php?moduleid="+moduleID;
		}			    
	     $.ajax({
			url: url,
		  	context: document.body
			}).done(function(data) {
		  	console.log(data);
		  	if(data){  		
				if (div.html()=="Import Module Content"){
					div.html("Submitting Module");
					div.delay(800)
				    .queue(function(n) {
				        div.html("Cancel Import");
				        n();
				        div.toggleClass("btn-success btn-danger");
				    }).fadeIn(100);		
				}
				else{
					div.html("Cancelling Import");
					div.delay(800)
				    .queue(function(n) {
				        div.html("Import Module Content");
				        n();
				        div.toggleClass("btn-success btn-danger");
				    }).fadeIn(100);		
				}
			}
			else{
				console.log(data);
			}
		});
	});			
});

