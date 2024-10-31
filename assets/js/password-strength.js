jQuery(document).ready(function ($) {
	'use strict';

	var password_fields = [
		[
			'#rcp_password', // Register form
			'#rcp_password_wrap'
		],
		[
			'#rcp_new_user_pass1', // Edit Profile password change form
			'#rcp_profile_password_wrap'
		],
		[
			'.rcp_change_password_fieldset #rcp_user_pass', // Password reset form
			'.rcp_change_password_fieldset > p:first-child'
		]
	];

	for (let i = 0; i < password_fields.length; i++) {
		let password_field = password_fields[i][0];
		let password_wrap = password_fields[i][1];

		$(password_field).after('<span id="rcp_user_pass_requirements">' + rcp_strong_passwords['requirements'] + '</span>');

		$(password_field).on('keyup', function (e) {

			var score = rcp_strong_passwords_get_length($(this));

			if (!document.getElementById('rcp_password_strength_meter')) {
				$(password_wrap).append('<span id="rcp_password_strength_meter"></span>');
			}

			$('#rcp_password_strength_meter').removeClass().addClass('rcp_password_strength_' + score).text(rcp_strong_passwords[score])

		});
	}

});

/**
 * Returns a password strength score:
 * 1 = very weak;
 * 2 = weak;
 * 3 = medium;
 * 4 = strong
 *
 * @param password
 * @returns {number}
 */
function rcp_strong_passwords_get_length(password) {

	var score = 0;
	var length = password.val().length;
	var username = jQuery('#rcp_user_login');
	var g, c;

	if (username.length > 0) {
		username = username.val();
	} else {
		username = rcp_strong_passwords['username'];
	}

	if (length < 4) {
		return 1;
	}

	if (password.val().toLowerCase() === username.toLowerCase()) {
		return 2;
	}

	if (password.val().match(/[0-9]/g)) {
		score += 10;
	}

	if (password.val().match(/[a-z]/g)) {
		score += 26;
	}

	if (password.val().match(/[A-Z]/g)) {
		score += 26;
	}

	if (password.val().match(/[^a-zA-Z0-9]/g)) {
		score += 31;
	}

	g = Math.log(Math.pow(score, length));
	c = g / Math.log(2);

	if (c < 40) {
		return 2;
	}

	if (c < 56) {
		return 3;
	}

	return 4;

}