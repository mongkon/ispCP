<?php
/**
 * ispCP ω (OMEGA) a Virtual Hosting Control System
 *
 * @copyright	2001-2006 by moleSoftware GmbH
 * @copyright	2006-2009 by ispCP | http://isp-control.net
 * @version		SVN: $Id: reseller_add.php 1744 2009-05-07 03:21:47Z haeber $
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

$tpl->define_dynamic('page', Config::get('ADMIN_TEMPLATE_PATH') . '/reseller_add.tpl');
$tpl->define_dynamic('page_message', 'page');
$tpl->define_dynamic('hosting_plans', 'page');
$tpl->define_dynamic('rsl_ip_message', 'page');
$tpl->define_dynamic('rsl_ip_list', 'page');
$tpl->define_dynamic('rsl_ip_item', 'rsl_ip_list');

$theme_color = Config::get('USER_INITIAL_THEME');

$tpl->assign(
	array(
		'TR_ADMIN_ADD_RESELLER_PAGE_TITLE' => tr('ispCP - Admin/Manage users/Add reseller'),
		'THEME_COLOR_PATH' => "../themes/$theme_color",
		'THEME_CHARSET' => tr('encoding'),
		'ISP_LOGO' => get_logo($_SESSION['user_id'])
	)
);

/**
 * Get Server IPs
 */
function get_server_ip(&$tpl, &$sql) {
	$query = "
		SELECT
			`ip_id`, `ip_number`, `ip_domain`
		FROM
			`server_ips`
		ORDER BY
			`ip_number`
	";

	$rs = exec_query($sql, $query, array());

	$i = 0;

	$reseller_ips = '';

	if ($rs->RecordCount() == 0) {
		$tpl->assign(
			array(
				'RSL_IP_MESSAGE' => tr('Reseller IP list is empty!'),
				'RSL_IP_LIST' => ''
			)
		);

		$tpl->parse('RSL_IP_MESSAGE', 'rsl_ip_message');
	} else {
		$tpl->assign(
			array(
				'TR_RSL_IP_NUMBER' => tr('No.'),
				'TR_RSL_IP_ASSIGN' => tr('Assign'),
				'TR_RSL_IP_LABEL' => tr('Label'),
				'TR_RSL_IP_IP' => tr('Number'),
			)
		);
		while (!$rs->EOF) {
			$tpl->assign(
				array(
					'RSL_IP_CLASS' => ($i % 2 == 0) ? 'content' : 'content2',
				)
			);

			$ip_id = $rs->fields['ip_id'];

			$ip_var_name = "ip_$ip_id";

			if (UserIO::POST_String($ip_var_name) == 'asgned') {
				$ip_item_assigned = 'checked="checked"';

				$reseller_ips .= "$ip_id;";
			} else {
				$ip_item_assigned = '';
			}

			$tpl->assign(
				array(
					'RSL_IP_NUMBER' => $i + 1,
					'RSL_IP_LABEL' => $rs->fields['ip_domain'],
					'RSL_IP_IP' => $rs->fields['ip_number'],
					'RSL_IP_CKB_NAME' => $ip_var_name,
					'RSL_IP_CKB_VALUE' => 'asgned',
					'RSL_IP_ITEM_ASSIGNED' => $ip_item_assigned,
				)
			);

			$tpl->parse('RSL_IP_ITEM', '.rsl_ip_item');

			$rs->MoveNext();

			$i++;
		}

		$tpl->parse('RSL_IP_LIST', 'rsl_ip_list');

		$tpl->assign('RSL_IP_MESSAGE', '');
	}

	return $reseller_ips;
}

function add_reseller(&$tpl, &$sql) {
	global $reseller_ips;

	if (UserIO::POST_String('uaction') == 'add_reseller') {
		if (check_user_data()) {
			$upass = crypt_user_pass(UserIO::POST_String('pass'));

			$user_id = $_SESSION['user_id'];

			$username = UserIO::POST_String('username');
			$fname = UserIO::POST_String('fname');
			$lname = UserIO::POST_String('lname');
			$gender = UserIO::POST_String('gender');
			$firm = UserIO::POST_String('firm');
			$zip = UserIO::POST_String('zip');
			$city = UserIO::POST_String('city');
			$state = UserIO::POST_String('state');
			$country = UserIO::POST_String('country');
			$email = UserIO::POST_String('email');
			$phone = UserIO::POST_String('phone');
			$fax = UserIO::POST_String('fax');
			$street1 = UserIO::POST_String('street1');
			$street2 = UserIO::POST_String('street2');

			$query = "
				INSERT INTO `admin` (
					`admin_name`,
					`admin_pass`,
					`admin_type`,
					`domain_created`,
					`created_by`,
					`fname`,
					`lname`,
					`firm`,
					`zip`,
					`city`,
					`state`,
					`country`,
					`email`,
					`phone`,
					`fax`,
					`street1`,
					`street2`,
					`gender`
				) VALUES (
					?,
					?,
					'reseller',
					unix_timestamp(),
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?
				)
			";
			$rs = exec_query($sql, $query, array($username,
					$upass,
					$user_id,
					$fname,
					$lname,
					$firm,
					$zip,
					$city,
					$state,
					$country,
					$email,
					$phone,
					$fax,
					$street1,
					$street2,
					$gender)
			);

			$new_admin_id = $sql->Insert_ID();

			$user_logged = $_SESSION['user_logged'];

			write_log("$user_logged: add reseller: $username");

			$user_def_lang = $_SESSION['user_def_lang'];
			$user_theme_color = $_SESSION['user_theme'];
			$user_logo = 0;

			$query = "
				INSERT INTO `user_gui_props`
					(
					`user_id`,
					`lang`,
					`layout`,
					`logo`
					)
				VALUES
					(?, ?, ?, ?)
			";

			$rs = exec_query($sql, $query, array($new_admin_id,
					$user_def_lang,
					$user_theme_color,
					$user_logo)
			);

			/*
			 * 'reseller_props' table entry;
			 */

			$nreseller_max_domain_cnt = UserIO::POST_Int('nreseller_max_domain_cnt');
			$nreseller_max_subdomain_cnt = UserIO::POST_Int('nreseller_max_subdomain_cnt');
			$nreseller_max_alias_cnt = UserIO::POST_Int('nreseller_max_alias_cnt');
			$nreseller_max_mail_cnt = UserIO::POST_Int('nreseller_max_mail_cnt');
			$nreseller_max_ftp_cnt = UserIO::POST_Int('nreseller_max_ftp_cnt');
			$nreseller_max_sql_db_cnt = UserIO::POST_Int('nreseller_max_sql_db_cnt');
			$nreseller_max_sql_user_cnt = UserIO::POST_Int('nreseller_max_sql_user_cnt');
			$nreseller_max_traffic = UserIO::POST_Int('nreseller_max_traffic');
			$nreseller_max_disk = UserIO::POST_Int('nreseller_max_disk');
			$customer_id = UserIO::POST_Int('customer_id');

			$query = "
				INSERT INTO `reseller_props` (
					`reseller_id`, `reseller_ips`,
					`max_dmn_cnt`, `current_dmn_cnt`,
					`max_sub_cnt`, `current_sub_cnt`,
					`max_als_cnt`, `current_als_cnt`,
					`max_mail_cnt`, `current_mail_cnt`,
					`max_ftp_cnt`, `current_ftp_cnt`,
					`max_sql_db_cnt`, `current_sql_db_cnt`,
					`max_sql_user_cnt`, `current_sql_user_cnt`,
					`max_traff_amnt`, `current_traff_amnt`,
					`max_disk_amnt`, `current_disk_amnt`,
					`customer_id`
				) VALUES (
					?, ?,
					?, '0',
					?, '0',
					?, '0',
					?, '0',
					?, '0',
					?, '0',
					?, '0',
					?, '0',
					?, '0',
					?
				)
				";

			$rs = exec_query($sql, $query, array($new_admin_id, $reseller_ips,
					$nreseller_max_domain_cnt,
					$nreseller_max_subdomain_cnt,
					$nreseller_max_alias_cnt,
					$nreseller_max_mail_cnt,
					$nreseller_max_ftp_cnt,
					$nreseller_max_sql_db_cnt,
					$nreseller_max_sql_user_cnt,
					$nreseller_max_traffic,
					$nreseller_max_disk,
					$customer_id)
			);

			send_add_user_auto_msg($user_id,
				UserIO::POST_String('username'),
				UserIO::POST_String('pass'),
				UserIO::POST_String('email'),
				UserIO::POST_String('fname'),
				UserIO::POST_String('lname'),
				tr('Reseller'),
				$gender
			);

			$_SESSION['reseller_added'] = 1;

			user_goto('manage_users.php');
		} else {
			$gender = UserIO::POST_String('gender');
			$tpl->assign(
				array(
					'EMAIL' => UserIO::HTML(UserIO::POST_String('email')),
					'USERNAME' => UserIO::HTML(UserIO::POST_String('username')),
					'FIRST_NAME' => UserIO::HTML(UserIO::POST_String('fname')),
					'CUSTOMER_ID' => UserIO::HTML(UserIO::POST_String('customer_id')),
					'LAST_NAME' => UserIO::HTML(UserIO::POST_String('lname')),
					'FIRM' => UserIO::HTML(UserIO::POST_String('firm')),
					'ZIP' => UserIO::HTML(UserIO::POST_String('zip')),
					'CITY' => UserIO::HTML(UserIO::POST_String('city')),
					'STATE' => UserIO::HTML(UserIO::POST_String('state')),
					'COUNTRY' => UserIO::HTML(UserIO::POST_String('country')),
					'STREET_1' => UserIO::HTML(UserIO::POST_String('street1')),
					'STREET_2' => UserIO::HTML(UserIO::POST_String('street2')),
					'PHONE' => UserIO::HTML(UserIO::POST_String('phone')),
					'FAX' => UserIO::HTML(UserIO::POST_String('fax')),
					'VL_MALE' => ($gender == 'M') ? 'selected="selected"' : '',
					'VL_FEMALE' => ($gender == 'F') ? 'selected="selected"' : '',
					'VL_UNKNOWN' => ($gender == 'U' || empty($gender)) ? 'selected="selected"' : ''),

					'MAX_DOMAIN_COUNT' => UserIO::HTML(UserIO::POST_String('nreseller_max_domain_cnt')),
					'MAX_SUBDOMAIN_COUNT' => UserIO::HTML(UserIO::POST_String('nreseller_max_subdomain_cnt')),
					'MAX_ALIASES_COUNT' => UserIO::HTML(UserIO::POST_String('nreseller_max_alias_cnt'),
					'MAX_MAIL_USERS_COUNT' => UserIO::HTML(UserIO::POST_String('nreseller_max_mail_cnt')),
					'MAX_FTP_USERS_COUNT' => UserIO::HTML(UserIO::POST_String('nreseller_max_ftp_cnt')),
					'MAX_SQLDB_COUNT' => UserIO::HTML(UserIO::POST_String('nreseller_max_sql_db_cnt')),
					'MAX_SQL_USERS_COUNT' => UserIO::HTML(UserIO::POST_String('nreseller_max_sql_user_cnt')),
					'MAX_TRAFFIC_AMOUNT' => UserIO::HTML(UserIO::POST_String('nreseller_max_traffic')),
					'MAX_DISK_AMOUNT' => UserIO::HTML(UserIO::POST_String('nreseller_max_disk'))
				)
			);
		}
	} // not add
	else {
		$tpl->assign(
			array(
				'EMAIL' => '',
				'USERNAME' => '',

				'FIRST_NAME' => '',
				'CUSTOMER_ID' => '',
				'LAST_NAME' => '',
				'FIRM' => '',
				'ZIP' => '',
				'CITY' => '',
				'STATE' => '',
				'COUNTRY' => '',
				'STREET_1' => '',
				'STREET_2' => '',
				'PHONE' => '',
				'FAX' => '',
				'VL_MALE' => '',
				'VL_FEMALE' => '',
				'VL_UNKNOWN' => 'selected="selected"',

				'MAX_DOMAIN_COUNT' => '',
				'MAX_SUBDOMAIN_COUNT' => '',
				'MAX_ALIASES_COUNT' => '',
				'MAX_MAIL_USERS_COUNT' => '',
				'MAX_FTP_USERS_COUNT' => '',
				'MAX_SQLDB_COUNT' => '',
				'MAX_SQL_USERS_COUNT' => '',
				'MAX_TRAFFIC_AMOUNT' => '',
				'MAX_DISK_AMOUNT' => ''
			)
		);
	}
}

function check_user_data() {
	global $reseller_ips;
	$sql = Database::getInstance();

	$username = UserIO::POST_String('username');

	$query = "
		SELECT
			`admin_id`
		FROM
			`admin`
		WHERE
			`admin_name` = ?
	";

	$rs = exec_query($sql, $query, array($username));

	if ($rs->RecordCount() != 0) {
		set_page_message(tr('This user name already exist!'));

		return false;
	}
	if (!chk_username($username)) {
		set_page_message(tr("Incorrect username length or syntax!"));

		return false;
	}
	if (!chk_password(UserIO::POST_String('pass'))) {
		if (Config::get('PASSWD_STRONG')) {
			set_page_message(sprintf(tr('The password must be at least %s long and contain letters and numbers to be valid.'), Config::get('PASSWD_CHARS')));
		} else {
			set_page_message(sprintf(tr('Password data is shorter than %s signs or includes not permitted signs!'), Config::get('PASSWD_CHARS')));
		}

		return false;
	}
	if (UserIO::POST_String('pass') != UserIO::POST_String('pass_rep')) {
		set_page_message(tr("Entered passwords do not match!"));

		return false;
	}
	if (UserIO::POST_EMail('email', true) === false) {
		set_page_message(tr("Incorrect email syntax!"));

		return false;
	}
	if (!ispcp_limit_check(UserIO::POST_String('nreseller_max_domain_cnt'), null)) {
		set_page_message(tr("Incorrect domains limit!"));

		return false;
	}
	if (!ispcp_limit_check(UserIO::POST_String('nreseller_max_subdomain_cnt'), -1)) {
		set_page_message(tr("Incorrect subdomains limit!"));

		return false;
	}
	if (!ispcp_limit_check(UserIO::POST_String('nreseller_max_alias_cnt'), -1)) {
		set_page_message(tr('Incorrect aliases limit!'));

		return false;
	}
	if (!ispcp_limit_check(UserIO::POST_String('nreseller_max_ftp_cnt'), -1)) {
		set_page_message(tr('Incorrect FTP accounts limit!'));

		return false;
	}
	if (!ispcp_limit_check(UserIO::POST_String('nreseller_max_mail_cnt'), -1)) {
		set_page_message(tr('Incorrect mail accounts limit!'));

		return false;
	}
	if (!ispcp_limit_check(UserIO::POST_String('nreseller_max_sql_db_cnt'), -1)) {
		set_page_message(tr('Incorrect SQL databases limit!'));

		return false;
	}
	if (!ispcp_limit_check(UserIO::POST_String('nreseller_max_sql_user_cnt'), -1)) {
		set_page_message(tr('Incorrect SQL users limit!'));

		return false;
	}
	if (!ispcp_limit_check(UserIO::POST_String('nreseller_max_traffic'), null)) {
		set_page_message(tr('Incorrect traffic limit!'));

		return false;
	}
	if (!ispcp_limit_check(UserIO::POST_String('nreseller_max_disk'), null)) {
		set_page_message(tr('Incorrect disk quota limit!'));

		return false;
	}
	if ($reseller_ips == '') {
		set_page_message(tr('You must assign at least one IP number for a reseller!'));

		return false;
	}

	return true;
}

/*
 *
 * static page messages.
 *
 */
gen_admin_mainmenu($tpl, Config::get('ADMIN_TEMPLATE_PATH') . '/main_menu_users_manage.tpl');
gen_admin_menu($tpl, Config::get('ADMIN_TEMPLATE_PATH') . '/menu_users_manage.tpl');

$reseller_ips = get_server_ip($tpl, $sql);

add_reseller($tpl, $sql);

$tpl->assign(
	array(
		'TR_ADD_RESELLER' => tr('Add reseller'),
		'TR_CORE_DATA' => tr('Core data'),
		'TR_USERNAME' => tr('Username'),
		'TR_PASSWORD' => tr('Password'),
		'TR_PASSWORD_REPEAT' => tr('Repeat password'),
		'TR_EMAIL' => tr('Email'),
		'TR_MAX_DOMAIN_COUNT' => tr('Domains limit<br><i>(0 unlimited)</i>'),
		'TR_MAX_SUBDOMAIN_COUNT' => tr('Subdomains limit<br><i>(-1 disabled, 0 unlimited)</i>'),
		'TR_MAX_ALIASES_COUNT' => tr('Aliases limit<br><i>(-1 disabled, 0 unlimited)</i>'),
		'TR_MAX_MAIL_USERS_COUNT' => tr('Mail accounts limit<br><i>(-1 disabled, 0 unlimited)</i>'),
		'TR_MAX_FTP_USERS_COUNT' => tr('FTP accounts limit<br><i>(-1 disabled, 0 unlimited)</i>'),
		'TR_MAX_SQLDB_COUNT' => tr('SQL databases limit<br><i>(-1 disabled, 0 unlimited)</i>'),
		'TR_MAX_SQL_USERS_COUNT' => tr('SQL users limit<br><i>(-1 disabled, 0 unlimited)</i>'),
		'TR_MAX_TRAFFIC_AMOUNT' => tr('Traffic limit [MB]<br><i>(0 unlimited)</i>'),
		'TR_MAX_DISK_AMOUNT' => tr('Disk limit [MB]<br><i>(0 unlimited)</i>'),
		'TR_PHP' => tr('PHP'),
		'TR_PERL_CGI' => tr('CGI / Perl'),
		'TR_JSP' => tr('JSP'),
		'TR_SSI' => tr('SSI'),
		'TR_FRONTPAGE_EXT' => tr('Frontpage extensions'),
		'TR_BACKUP_RESTORE' => tr('Backup and restore'),
		'TR_CUSTOM_ERROR_PAGES' => tr('Custom error pages'),
		'TR_PROTECTED_AREAS' => tr('Protected areas'),
		'TR_WEBMAIL' => tr('Webmail'),
		'TR_DIR_LIST' => tr('Directory listing'),
		'TR_APACHE_LOGFILES' => tr('Apache logfiles'),
		'TR_AWSTATS' => tr('AwStats'),
		'TR_LOGO_UPLOAD' => tr('Logo upload'),
		'TR_YES' => tr('yes'),
		'TR_NO' => tr('no'),

		'TR_RESELLER_IPS' => tr('Reseller IPs'),

		'TR_ADDITIONAL_DATA' => tr('Additional data'),
		'TR_CUSTOMER_ID' => tr('Customer ID'),
		'TR_FIRST_NAME' => tr('First name'),
		'TR_LAST_NAME' => tr('Last name'),
		'TR_LAST_NAME' => tr('Last name'),
		'TR_GENDER' => tr('Gender'),
		'TR_MALE' => tr('Male'),
		'TR_FEMALE' => tr('Female'),
		'TR_UNKNOWN' => tr('Unknown'),
		'TR_COMPANY' => tr('Company'),
		'TR_ZIP_POSTAL_CODE' => tr('Zip/Postal code'),
		'TR_CITY' => tr('City'),
		'TR_STATE' => tr('State/Province'),
		'TR_COUNTRY' => tr('Country'),
		'TR_STREET_1' => tr('Street 1'),
		'TR_STREET_2' => tr('Street 2'),
		'TR_PHONE' => tr('Phone'),
		'TR_FAX' => tr('Fax'),
		'TR_PHONE' => tr('Phone'),
		'TR_ADD' => tr('Add'),
		'GENPAS' => passgen()
	)
);

gen_page_message($tpl);

$tpl->parse('PAGE', 'page');
$tpl->prnt();

if (Config::get('DUMP_GUI_DEBUG')) {
	dump_gui_debug();
}
unset_messages();
