<!DOCTYPE html>
<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
	// https://code.tutsplus.com/tutorials/submit-a-form-without-page-refresh-using-jquery--net-59
	$(function() {
		$("#register_btn").click(function() {
			var eesnimi = $("input#eesnimi").val();
			var perenimi = $("input#perenimi").val();
			var epost = $("input#epost").val();
			var parool = $("input#parool").val();
			var telefon = $("input#telefon").val();
			var dataString = 'eesnimi='+ eesnimi + '&perenimi=' + perenimi + '&epost=' + epost + '&parool=' + parool + '&telefon=' + telefon;
			var formData = {
					eesnimi: $("input#eesnimi").val(),
					perenimi: $("input#perenimi").val(),
					epost: $("input#epost").val(),
					parool: $("input#parool").val(),
					telefon: $("input#telefon").val()
				};

			$.ajax({
				type: "POST",
				url: "<?php echo dirname($_ENV['SCRIPT_NAME']); ?>/function/create_account.php",
				/*dataType: 'json',
				contentType: 'application/json',*/
				data: dataString,//JSON.stringify(formData),
				success: function() {
					$('#contact_form').html("<div id='message'></div>");
					$('#message').html("<h2>Contact Form Submitted!</h2>")
					.append("<p>We will be in touch soon.</p>")
					.hide()
					.fadeIn(1500, function() {
						$('#message').append("<img id='checkmark' src='images/check.png' />");
					});
				},
				error: function( jqXhr, textStatus, errorThrown ){
			        console.log( errorThrown );
			    }
			});
		});
	});
</script>
</head>
<body>
<?php
require_once( __DIR__ . "/Database.php" );

$Connection = new \Back\Connection("142.93.135.89", "editor", "mnJfFNl8mF0ptbIE", "veebirakendus");
$DatabaseHandler = new \Back\DatabaseHandler($Connection);
//$DatabaseHandler->create_account("mats@gmail.com", "abc", "Madis", "Männik", "56111111");
//echo var_dump($_ENV) . "<br>";

?>
<div id="register-form">

<form name="register" action="" method="post">
	<fieldset>
	E-post: <input type="text" id="epost"><br>
	Parool: <input type="password" id="parool"><br>
	Eesnimi: <input type="text" id="eesnimi"><br>
	Perenimi: <input type="text" id="perenimi"><br>
	Telefon: <input type="text" id="telefon"><br>
	<input type="submit" value="Submit" id="register_btn">
	</fieldset>
</form>
<br>
</div>
<?php

foreach( $DatabaseHandler->fetch_accounts([]) as $dp ){
	echo $dp->getJson() . "<br>";
}
?>
</body>
</html>