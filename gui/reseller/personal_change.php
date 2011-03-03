<?php
/**
 * ispCP ω (OMEGA) a Virtual Hosting Control System
 *
 * @copyright 	2001-2006 by moleSoftware GmbH
 * @copyright 	2006-2011 by ispCP | http://isp-control.net
 * @version 	SVN: $Id$
 * @link 		http://isp-control.net
 * @author 		ispCP Team
 *
 * @license
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "VHCS - Virtual Hosting Control System".
 *
 * The Initial Developer of the Original Code is moleSoftware GmbH.
 * Portions created by Initial Developer are Copyright (C) 2001-2006
 * by moleSoftware GmbH. All Rights Reserved.
 * Portions created by the ispCP Team are Copyright (C) 2006-2011 by
 * isp Control Panel. All Rights Reserved.
 */

require '../include/ispcp-lib.php';

check_login(__FILE__);

$cfg = ispCP_Registry::get('Config');

$tpl = ispCP_TemplateEngine::getInstance();
$template = 'personal_change.tpl';

if (isset($_POST['uaction']) && $_POST['uaction'] === 'updt_data') {
	update_reseller_personal_data($sql, $_SESSION['user_id']);
}

gen_reseller_personal_data($tpl, $sql, $_SESSION['user_id']);


// static page messages
gen_logged_from($tpl);

$tpl->assign(
	array(
		'TR_PAGE_TITLE'				=> tr('ispCP - Reseller/Change Personal Data'),
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
		'TR_UPDATE_DATA'			=> tr('Update data'),
	)
);

gen_reseller_mainmenu($tpl, 'main_menu_general_information.tpl');
gen_reseller_menu($tpl, 'menu_general_information.tpl');

gen_page_message($tpl);

$tpl->display($template);

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug();
}
unset_messages();

/**
 * @param ispCP_TemplateEngine $tpl
 * @param ispCP_Database $sql
 * @param int $user_id
 */
function gen_reseller_personal_data(&$tpl, &$sql, $user_id) {
	$cfg = ispCP_Registry::get('Config');

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

	$rs = exec_query($sql, $query, $user_id);

	$tpl->assign(
		array(
			'FIRST_NAME'	=> (($rs->fields['fname'] == null)		? '' : tohtml($rs->fields['fname'])),
			'LAST_NAME'		=> (($rs->fields['lname'] == null)		? '' : tohtml($rs->fields['lname'])),
			'FIRM'			=> (($rs->fields['firm'] == null)		? '' : tohtml($rs->fields['firm'])),
			'ZIP'			=> (($rs->fields['zip'] == null)		? '' : tohtml($rs->fields['zip'])),
			'CITY'			=> (($rs->fields['city'] == null)		? '' : tohtml($rs->fields['city'])),
			'STATE'			=> (($rs->fields['state'] == null)		? '' : tohtml($rs->fields['state'])),
			'COUNTRY'		=> (($rs->fields['country'] == null)	? '' : tohtml($rs->fields['country'])),
			'STREET_1'		=> (($rs->fields['street1'] == null)	? '' : tohtml($rs->fields['street1'])),
			'STREET_2'		=> (($rs->fields['street2'] == null)	? '' : tohtml($rs->fields['street2'])),
			'EMAIL'			=> (($rs->fields['email'] == null)		? '' : tohtml($rs->fields['email'])),
			'PHONE'			=> (($rs->fields['phone'] == null)		? '' : tohtml($rs->fields['phone'])),
			'FAX'			=> (($rs->fields['fax'] == null)		? '' : tohtml($rs->fields['fax'])),
			'VL_MALE'		=> (($rs->fields['gender'] == 'M')		? $cfg->HTML_SELECTED : ''),
			'VL_FEMALE'		=> (($rs->fields['gender'] == 'F')		? $cfg->HTML_SELECTED : ''),
			'VL_UNKNOWN'	=> ((($rs->fields['gender'] == 'U') || (empty($rs->fields['gender']))) ? $cfg->HTML_SELECTED : '')
		)
	);
}

function update_reseller_personal_data(&$sql, $user_id) {
	$fname		= clean_input($_POST['fname']);
	$lname		= clean_input($_POST['lname']);
	$gender		= $_POST['gender'];
	$firm		= clean_input($_POST['firm']);
	$zip		= clean_input($_POST['zip']);
	$city		= clean_input($_POST['city']);
	$state		= clean_input($_POST['state']);
	$country	= clean_input($_POST['country']);
	$street1	= clean_input($_POST['street1']);
	$street2	= clean_input($_POST['street2']);
	$email		= clean_input($_POST['email']);
	$phone		= clean_input($_POST['phone']);
	$fax		= clean_input($_POST['fax']);

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
			`email` = ?,
			`phone` = ?,
			`fax` = ?,
			`street1` = ?,
			`street2` = ?,
			`gender` = ?
		WHERE
			`admin_id` = ?
	";

	// NXW: Unused variable so...
	// $rs = exec_query($sql, $query, array($fname, $lname, $firm, $zip, $city, $state, $country, $email, $phone, $fax, $street1, $street2, $gender, $user_id));
	exec_query(
			$sql,
			$query,
			array(
				$fname, $lname, $firm, $zip, $city, $state, $country, $email,
				$phone, $fax, $street1, $street2, $gender, $user_id
			)
	);

	set_page_message(tr('Personal data updated successfully!'), 'success');
}
?>
