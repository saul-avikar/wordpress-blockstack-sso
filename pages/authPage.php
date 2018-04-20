<?php
/*
	Template Name: Authentication page
	Author: Saul Boyd (avikar.io)
	License: GPL (http://www.gnu.org/copyleft/gpl.html)
*/
?>
<html>
<head>
	<?php
	wp_enqueue_style( 'login' );
	do_action( 'login_head' );
	?>
	<title>Blockstack Login</title>
</head>
	<body class="login login-action-login wp-core-ui  locale-en-us">
		<div id="login">
			<form id="loginform">
				<h3 id="message">
					Logging in!
				</h3>
				<div id="details" class="hidden">
					<p><label for="username">Username: <input class="input" id="username" type="text" /></label></p>
					<p><label for="password">Password: <input class="input" id="password" type="password" /></label></p>
					<p class="submit">
						<input class="button button-primary button-large" type="button" id="resubmitDetails" value="Submit" />
					</p>
				</div>
			</form>
		</div>

		<div class="clear"></div>

		<style>
			#message {
				border: 2px solid black;
				padding: 10px;
			}

			#details{
				margin-top: 10px;
			}

			.error{
				border: 2px solid red !important;
				color: red;
			}

			.hidden {
				display: none;
			}

			.login form{
				padding: 24px !important;
			}
		</style>
		<!-- include the blockstack file -->
		<script src="<?php echo "http" . ( ( is_ssl() ) ? 's' : '' ) . "://" . $_SERVER['SERVER_NAME'] . "/" . get_option( 'blockstack_jsLibraryLocation' ); ?>"></script>
		<script>
			var messageEl = document.getElementById("message");
			var detailsEl = document.getElementById("details");
			var linking = ( "<?php echo get_option( 'blockstack_accountLinking' ) ?>" === "on" );
			var custom = ( "<?php echo get_option( 'blockstack_customUsernames' ) ?>" === "on" );
			var creation = ( "<?php echo get_option( 'blockstack_accountCreation' ) ?>" === "on" );

			Blockstack_sso.isSignedIn().then( ( userData ) => {
				// successful sign in
				var url = "<?php echo plugin_dir_url( __FILE__ ) . 'auth.php' ?>";

				if ( linking || custom ) {
					Blockstack_sso.getLoginDetails().then( function ( res ) {
						if ( !res.username || res.username == "" || !res.password || res.password == "" ) {
							// There is a problem in the username or password

							detailsEl.className = "";
							messageEl.innerHTML = ( linking && !creation) ? "Please login using your wordpress deatils to link your account." :
								"Please enter existing wordpress account details to link it to blockstack or enter new ones to create an account.";

							document.getElementById("username").value = userData.username ? userData.username : ( userData.profile.name ? userData.profile.name : res.username );
						}
						else {
							attemptSignin( userData, url );
						}
					}).catch( function ( err ) {
						// Initially it doesn't seem logical to do this on error but all the error means is that the function is failing to retrieve/parse the data
						// So we allow our backend logic to deal with it per settings

						attemptSignin( userData, url );
					});
				}
				else {
					attemptSignin( userData, url );
				}

				document.getElementById("resubmitDetails").addEventListener("click", function() {
					var username = document.getElementById("username").value;
					var password = document.getElementById("password").value;

					detailsEl.className = "hidden";
					messageEl.innerHTML =  "Logging in!";
					detailsEl.className = "";
					messageEl.className = "";

					Blockstack_sso.setLoginDetails( username, password ).then( function (res) {
						attemptSignin( userData, url );
					});
				});
			}).catch( ( err ) => {
				// sign in failed.
				console.error(err);
				document.getElementById("message").innerHTML =  err.message;
				messageEl.className = "error";
			});

			function attemptSignin( userData, url ){
				Blockstack_sso.phpSignIn( userData, url ).then( ( res ) => {
					console.log(res);
					messageEl.className = "";
					if ( res.request ) {
						// sign in is requesting user details
						detailsEl.className = "";
						messageEl.innerHTML = res.message;
					}
					else {
						// successful sign-in
						messageEl.innerHTML =  "Success!";

						window.location.replace( "http:\/\/" + window.location.hostname + "/wp-admin/" );
					}
				}).catch( ( err ) => {
					// failed for some reason or another
					console.error(err);
					messageEl.innerHTML =  err.message;
					messageEl.className = "error";
				});
			}
		</script>
	</body>
</html>