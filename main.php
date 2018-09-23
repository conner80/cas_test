<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Expires" content="0" />
  	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Test case the game</title>
	<link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/jquery-ui.css">
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
        <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script type="text/javascript" src="js/util.js"></script>
  	<style>
    	   label, input { display:block; }
    	   input.text { margin-bottom:12px; width:95%; padding: .4em; }
    	   fieldset { padding:0; border:0; margin-top:25px; }
    	   h1 { font-size: 1.2em; margin: .6em 0; }
    	   .ui-dialog .ui-state-error { padding: .3em; }
    	   .validateTips { border: 1px solid transparent; padding: 0.3em; }
  	</style>
  	<script>
  	   $(function() {
    	   var dialog, form, flg = 0,
 
      		tips = $(".validateTips");
 
    		function updateTips( t ) {
      		   tips
        	   .text( t )
                   .addClass( "ui-state-highlight" );
                   setTimeout(function() {
                    tips.removeClass( "ui-state-highlight", 1500 );
                   }, 500 );
                }
 
                function ShowDlg() {
                   var sess = getParameterByName('id');
                   var valid = true;
                   var sel = 0;
                   var msg;  
 
                   if (sess === undefined) {
                     window.location.href = 'index.php';
                     return false;
                   }
              
//                   allFields.removeClass( "ui-state-error" );
 

                   var chk = document.getElementById('getprize').checked;
                   if (chk) 
                     sel = 1;

                   chk = document.getElementById('exchange').checked;   
                   if (chk)
                     sel = 2;

                   if (!sel) {
                     msg = "You must make a choose!";              
                     valid = false;
                   }

                   if ( valid ) {
                     $.ajax({
                        url: "run.php",
                        type: "post",
                        async: false,
                        data: {
                                session_id: sess,
                                action: "get",
                                choose: sel
                              }, 
                        success: function (data) {
                           alert(data);   
                           if (data.trim() != '') {
                             var dat = JSON.parse(data);
                             var msg = dat.error;
                             if (msg !== undefined) {
                               if (msg == 'logout')
                                 window.location.href = 'index.php'
                               else
                                 valid = false;

                               if (!valid) {
//                                 o.addClass( "ui-state-error" );
//                                 updateTips( msg );
                               }
                             } else {
                               dialog.dialog( "close" );
                             }
                           }    
                        }
                     });

                     dialog.dialog( "close" );
                   } else {
//                     o.addClass( "ui-state-error" );
//                     updateTips( msg );
                   }

                   return valid;
                }
 
                dialog = $( "#dialog-form1" ).dialog({
                   autoOpen: false,
                   height: 300,
                   width: 350,
                   modal: true,
                   buttons: {
                      "Accept": ShowDlg,
                      Cancel: function() {
                        dialog.dialog( "close" );
                      }
                   },
                   close: function() {
                      form[ 0 ].reset();
//                      allFields.removeClass( "ui-state-error" );
                      $( this ).dialog( "destroy" ); 
                   }
                });
 
                form = dialog.find( "form" ).on( "submit", function( event ) {
                   event.preventDefault();
                   ShowDlg();
                });
 
           $( "#run" ).button().on( "click", function() {
              var sess = getParameterByName('id');
              if (sess === undefined) {
                window.location.href = 'index.php';
                return;
              }

              $.ajax({
                url: "run.php",
                type: "post",
                async: false,
                data: {
                  session_id: sess,
                  action: "run"
                       }, 
                success: function (data) {
                   if (data.trim() != '') {
                     var dat = JSON.parse(data);
                     var msg = dat.error;
                     if (msg !== undefined) {
                       if (msg == 'logout')
                         window.location.href = 'index.php';
                     } else { 
                       var typ = dat.type;
                       var cnt = dat.count;
                       var nam = dat.name;

                       if (typ !== undefined && cnt !== undefined && nam !== undefined) {
                         switch (typ) {
                           case 1:
                             $('#message').html('You won ' + cnt + nam);
                             break;
                           case 2:     
                             $('#message').html('You won a '+nam+'!');
                             break;
                           case 3:     
                             $('#message').html('You won ' + cnt + ' coins!');
                             document.getElementById("exchange").style.visibility = "visible";
                             document.getElementById("lblexchg").style.visibility = "visible";
                             break;
                           default:
                             typ = 0;
                             break;  
                           }

                          if (typ)
                            dialog.dialog( "open" );
                       }
                     }  
                   } 
                }
              });
           });
         } );
  </script>

</head>

<body>
<div id="dialog-form1" title="Congratulation">
  <p id="message" class="validateTips"></p>
 
  <form>
    <fieldset>
       <legend>Select action</legend>  
       <ul>
         <li>
           <dl>
             <dt><label id="lblget" for="getprize">Get a prize</label></dt>
             <dd><input type="radio" class="radio_" id="getprize"
                   name="choose" value="prize" checked></dd>
           </dl>
         </li>
         <li>
           <dl> 
             <dt><label id="lblexchg" for="exchange" style="visibility:hidden;">Exchange to points</label></dt>
             <dd><input type="radio" class="radio_" id="exchange"
                   name="choose" value="points" style="visibility:hidden;"></dd>
           </dl> 
         </li>
       </ul>         
      <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
    </fieldset>
  </form>
</div>

<div id="main_form" class="ui-widget">
   <h1>Main form</h1>
   <fieldset id="inputs">
   <img src="images/box.jpg" alt="The box with a prize" class="box-img" />
   </fieldset>
   <fieldset id="actions">
     <button id="run" name="run" type="button">GET A PRIZE</button> 
   </fieldset>
</div>

</body>
</html>
