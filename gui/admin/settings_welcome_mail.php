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
$template = 'settings_welcome_mail.tpl';

$user_id = $_SESSION['user_id'];

$data = get_welcome_email($user_id, 'reseller');

if (isset($_POST['uaction']) && $_POST['uaction'] == 'email_setup') {

	$data['subject'] = clean_input($_POST['auto_subject'], false);
	$data['message'] = clean_input($_POST['auto_message'], false);

	$message = '';

	if (empty($data['subject'])) {
		$message .= tr('Please specify a subject!') . '<br />';
	}
	if (empty($data['message'])) {
		$message .= tr('Please specify message!');
	}

	if (!empty($message)) {
		set_page_message($message, 'warning');
	} else {
		set_welcome_email($user_id, $data);
		set_page_message(tr('Auto email template data updated!'), 'notice');
	}
}

// static page messages
$tpl->assign(
	array(
		'TR_PAGE_TITLE' => tr('ispCP - Admin/Manage users/Email setup'),
		'TR_EMAIL_SETUP' => tr('Email setup'),
		'TR_MESSAGE_TEMPLATE_INFO' => tr('Message template info'),
		'TR_USER_LOGIN_NAME' => tr('User login (system) name'),
		'TR_USER_PASSWORD' => tr('User password'),
		'TR_USER_REAL_NAME' => tr('User real (first and last) name'),
		'TR_MESSAGE_TEMPLATE' => tr('Message template'),
		'TR_SUBJECT' => tr('Subject'),
		'TR_MESSAGE' => tr('Message'),
		'TR_SENDER_EMAIL' => tr('Senders email'),
		'TR_SENDER_NAME' => tr('Senders name'),
		'TR_APPLY_CHANGES' => tr('Apply changes'),
		'TR_USERTYPE' => tr('User type (admin, reseller, user)'),
		'TR_BASE_SERVER_VHOST' => tr('URL to this admin panel'),
		'TR_BASE_SERVER_VHOST_PREFIX' => tr('URL protocol'),
		'SUBJECT_VALUE' => tohtml($data['subject']),
		'MESSAGE_VALUE' => tohtml($data['message']),
		'SENDER_EMAIL_VALUE' => tohtml($data['sender_email']),
		'SENDER_NAME_VALUE' => tohtml($data['sender_name'])
	)
);

gen_admin_mainmenu($tpl, 'main_menu_settings.tpl');
gen_admin_menu($tpl, 'menu_settings.tpl');

gen_page_message($tpl);

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug($tpl);
}

$tpl->display($template);

unset_messages();
?>
