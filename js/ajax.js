$(function() {
  $('button').click(function() {
        var btn = this.id;
        if (btn == "submit") {
          var user = $('#username').val();
          var pass = $('#password').val(); 
    	  $.ajax({
	    url: "login.php",
	    type: "post",
            async: false,
	    data: {
	 	   username: user,
		   password: pass
		  },
	    success: function (data) {
	       if (data.trim() != '') {
	         var dat = JSON.parse(data);
	         var st = dat.message;
                 var ts = dat.session_id;
                 if (st !== undefined)
	           $('#results').html(st)
                 else if (ts !== undefined)
                   window.location.href = 'main.php?id='+ts;
               }
	    } 
	  });
        }
	return false;
  });
});

