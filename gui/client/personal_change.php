<?php
/**
 * ispCP ω (OMEGA) a Virtual Hosting Control System
 *
 * @copyright	2001-2006 by moleSoftware GmbH
 * @copyright	2006-2009 by ispCP | http://isp-control.net
 * @version		SVN: $Id: personal_change.php 1746 2009-05-08 03:38:52Z haeber $
 * @link		http://isp-control.net
 * @author		ispCP Team
 *
 * @license
 *   This program is free software; you can redistribute it and/or modify it under
 *   the terms of the MPL General Public License as published by the Free Software
 *   Foundation; either version 1.1 of the License, or (at your option) any later
 *   version.
 *   You should have received a copy of the MPL Mozilla Public License along with
 *   this program; if not, write to the Open Source Initiative (OSI)
 *   http://opensource.org | osi@opensource.org
 */

require '../include/ispcp-lib.php';

check_login(__FILE__);

$tpl = new pTemplate();
$tpl->define_dynamic('page', Config::get('CLIENT_TEMPLATE_PATH') . '/personal_change.tpl');
$tpl->define_dynamic('page_message', 'page');
$tpl->define_dynamic('logged_from', 'page');

$theme_color = Config::get('USER_INITIAL_THEME');

$tpl->assign(
	array(
		'TR_CLIENT_CHANGE_PERSONAL_DATA_PAGE_TITLE' => tr('ispCP - Client/Change Personal Data'),
		'THEME_COLOR_PATH' => "../themes/$theme_color",
		'THEME_CHARSET' => tr('encoding'),
		'ISP_LOGO' => get_logo($_SESSION['user_id'])
	)
);

if (UserIO::POST_String('uaction') == 'updt_data') {
	update_user_personal_data($sql, $_SESSION['user_id']);
}

gen_user_personal_data($tpl, $sql, $_SESSION['user_id']);

function gen_user_personal_data(&$tpl, &$sql, $user_id) {
	$query = "
		SELECT
			`fname`,
			`lname`,
			`gender`,
			`firm`,
			`zip`,
			`city`,
			`state`,
			`country`,
			`street1`,
			`street2`,
			`email`,
			`phone`,
			`fax`
		FROM
			`admin`
		WHERE
			`admin_id` = ?
	";

	$rs = exec_query($sql, $query, array($user_id));
	$tpl->assign(
		array(
			'FIRST_NAME'	=> empty($rs->fields['fname']) ? '' : UserIO::HTML($rs->fields['fname']),
			'LAST_NAME'		=> empty($rs->fields['lname']) ? '' : UserIO::HTML($rs->fields['lname']),
			'FIRM'			=> empty($rs->fields['firm']) ? '' : UserIO::HTML($rs->fields['firm']),
			'ZIP'			=> empty($rs->fields['zip']) ? '' : UserIO::HTML($rs->fields['zip']),
			'CITY'			=> empty($rs->fields['city']) ? '' : UserIO::HTML($rs->fields['city']),
			'STATE'			=> empty($rs->fields['state']) ? '' : UserIO::HTML($rs->fields['state']),
			'COUNTRY'		=> empty($rs->fields['country']) ? '' : UserIO::HTML($rs->fields['country']),
			'STREET_1'		=> empty($rs->fields['street1']) ? '' : UserIO::HTML($rs->fields['street1']),
			'STREET_2'		=> empty($rs->fields['street2']) ? '' : UserIO::HTML($rs->fields['street2']),
			'EMAIL'			=> empty($rs->fields['email']) ? '' : UserIO::HTML($rs->fields['email']),
			'PHONE'			=> empty($rs->fields['phone']) ? '' : UserIO::HTML($rs->fields['phone']),
			'FAX'			=> empty($rs->fields['fax']) ? '' : UserIO::HTML($rs->fields['fax']),
			'VL_MALE'		=> (($rs->fields['gender'] == 'M') ? 'selected="selected"' : ''),
			'VL_FEMALE'		=> (($rs->fields['gender'] == 'F') ? 'selected="selected"' : ''),
			'VL_UNKNOWN'	=> ((($rs->fields['gender'] == 'U') || (empty($rs->fields['gender']))) ? 'selected="selected"' : '')
		)
	);
}

function update_user_personal_data(&$sql, $user_id) {
	$fname = UserIO::POST_String('fname');
	$lname = UserIO::POST_String('lname');
	$gender = UserIO::POST_String('gender');
	$firm = UserIO::POST_String('firm');
	$zip = UserIO::POST_String('zip');
	$city = UserIO::POST_String('city');
	$state = UserIO::POST_String('state');
	$country = UserIO::POST_String('country');
	$street1 = UserIO::POST_String('street1');
	$street2 = UserIO::POST_String('street2');
	$email = UserIO::POST_String('email');
	$phone = UserIO::POST_String('phone');
	$fax = UserIO::POST_String('fax');

	$query = "
		UPDATE
			`admin`
		SET
			`fname` = ?,
			`lname` = ?,
			`firm` = ?,
			`zip` = ?,
			`city` = ?,
			`state` = ?,
			`country` = ?,
			`street1` = ?,
			`street2` = ?,
			`email` = ?,
			`phone` = ?,
			`fax` = ?,
			`gender` = ?
		WHERE
			`admin_id` = ?
	";

	$rs = exec_query($sql, $query, array($fname, $lname, $firm, $zip, $city, $state, $country, $street1, $street2, $email, $phone, $fax, $gender, $user_id));

	write_log($_SESSION['user_logged'] . ": update personal data");
	set_page_message(tr('Personal data updated successfully!'));
}

/*
 *
 * static page messages.
 *
 */

gen_client_mainmenu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/main_menu_general_information.tpl');
gen_client_menu($tpl, Config::get('CLIENT_TEMPLATE_PATH') . '/menu_general_information.tpl');

gen_logged_from($tpl);

check_permissions($tpl);

$tpl->assign(
	array(
		'TR_CHANGE_PERSONAL_DATA'	=> tr('Change personal data'),
		'TR_PERSONAL_DATA'			=> tr('Personal data'),
		'TR_FIRST_NAME'				=> tr('First name'),
		'TR_LAST_NAME'				=> tr('Last name'),
		'TR_COMPANY'				=> tr('Company'),
		'TR_ZIP_POSTAL_CODE'		=> tr('Zip/Postal code'),
		'TR_CITY'					=> tr('City'),
		'TR_STATE'					=> tr('State/Province'),
		'TR_COUNTRY'				=> tr('Country'),
		'TR_STREET_1'				=> tr('Street 1'),
		'TR_STREET_2'				=> tr('Street 2'),
		'TR_EMAIL'					=> tr('Email'),
		'TR_PHONE'					=> tr('Phone'),
		'TR_FAX'					=> tr('Fax'),
		'TR_GENDER'					=> tr('Gender'),
		'TR_MALE'					=> tr('Male'),
		'TR_FEMALE'					=> tr('Female'),
		'TR_UNKNOWN'				=> tr('Unknown'),
		'TR_UPDATE_DATA'			=> tr('Update data')
	)
);

gen_page_message($tpl);
$tpl->parse('PAGE', 'page');
$tpl->prnt();

if (Config::get('DUMP_GUI_DEBUG')) {
	dump_gui_debug();
}
unset_messages();
