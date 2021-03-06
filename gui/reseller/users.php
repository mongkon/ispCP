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
$template = 'users.tpl';

// TODO: comment!
unset($_SESSION['dmn_name']);
unset($_SESSION['ch_hpprops']);
unset($_SESSION['local_data']);
unset($_SESSION['dmn_ip']);
unset($_SESSION['dmn_id']);
unset($GLOBALS['dmn_name']);
unset($GLOBALS['ch_hpprops']);
unset($GLOBALS['local_data']);
unset($GLOBALS['user_add3_added']);
unset($GLOBALS['user_add3_added']);
unset($GLOBALS['dmn_ip']);
unset($GLOBALS['dmn_id']);

// static page messages
gen_logged_from($tpl);

$crnt_month = date("m");
$crnt_year = date("Y");

$tpl->assign(
	array(
		'TR_PAGE_TITLE'				=> tr('ispCP - Users'),
		'TR_MANAGE_USERS'			=> tr('Manage users'),
		'TR_USERS'					=> tr('Users'),
		'TR_USER_STATUS'			=> tr('Status'),
		'TR_DETAILS'				=> tr('Details'),
		'TR_SEARCH'					=> tr('Search'),
		'TR_USERNAME'				=> tr('Username'),
		'TR_ACTION'					=> tr('Actions'),
		'TR_CREATION_DATE'			=> tr('Creation date'),
		'TR_EXPIRE_DATE'			=> tr('Expire date'),
		'TR_CHANGE_USER_INTERFACE'	=> tr('Switch to user interface'),
		'TR_BACK'					=> tr('Back'),
		'TR_TITLE_BACK'				=> tr('Return to previous menu'),
		'TR_TABLE_NAME'				=> tr('Users list'),
		'TR_MESSAGE_CHANGE_STATUS'	=> tr('Are you sure you want to change the status of %s?', true, '%s'),
		'TR_MESSAGE_DELETE_ACCOUNT'	=> tr('Are you sure you want to delete %s?', true, '%s'),
		'TR_STAT'					=> tr('Stats'),
		'VL_MONTH'					=> $crnt_month,
		'VL_YEAR'					=> $crnt_year,
		'TR_EDIT_DOMAIN'			=> tr('Edit Domain'),
		'TR_EDIT_USER'				=> tr('Edit User'),
		'TR_BW_USAGE'				=> tr('Bandwidth'),
		'TR_DISK_USAGE'				=> tr('Disk'),
		'TR_DELETE'					=> tr('Delete')
	)
);

gen_reseller_mainmenu($tpl, 'main_menu_users_manage.tpl');
gen_reseller_menu($tpl, 'menu_users_manage.tpl');

if (isset($cfg->HOSTING_PLANS_LEVEL)
	&& $cfg->HOSTING_PLANS_LEVEL === 'admin') {
	$tpl->assign('EDIT_OPTION', '');
}

generate_users_list($tpl, $_SESSION['user_id']);
check_externel_events($tpl);
gen_page_message($tpl);

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug($tpl);
}

$tpl->display($template);

unset_messages();

// Begin function block

/**
 * @param ispCP_TemplateEngine $tpl
 * @param int $admin_id
 */
function generate_users_list($tpl, $admin_id) {

	$sql = ispCP_Registry::get('Db');
	$cfg = ispCP_Registry::get('Config');

	$rows_per_page = $cfg->DOMAIN_ROWS_PER_PAGE;

	if (isset($_POST['details']) && !empty($_POST['details'])) {
		$_SESSION['details'] = $_POST['details'];
	} else {
		if (!isset($_SESSION['details'])) {
			$_SESSION['details'] = "hide";
		}
	}

    if (isset($_GET['psi']) && $_GET['psi'] == 'last') {
        if (isset($_SESSION['search_page'])) {
            $_GET['psi'] = $_SESSION['search_page'];
        } else {
            unset($_GET['psi']);
        }
    }

	// Search request generated?
	if (isset($_POST['search_for']) && !empty($_POST['search_for'])) {
		$_SESSION['search_for'] = trim(clean_input($_POST['search_for']));

		$_SESSION['search_common'] = $_POST['search_common'];

		$_SESSION['search_status'] = $_POST['search_status'];

		$start_index = 0;
	} else {
        $start_index = isset($_GET['psi']) ? (int)$_GET['psi'] : 0;
        
		if (isset($_SESSION['search_for']) && !isset($_GET['psi'])) {
			// He have not got scroll through patient records.
			unset($_SESSION['search_for']);
			unset($_SESSION['search_common']);
			unset($_SESSION['search_status']);
		} 
	}

    $_SESSION['search_page'] = $start_index;

	$search_query = '';
	$count_query = '';

	if (isset($_SESSION['search_for'])) {
		gen_manage_domain_query($search_query,
			$count_query,
			$admin_id,
			$start_index,
			$rows_per_page,
			$_SESSION['search_for'],
			$_SESSION['search_common'],
			$_SESSION['search_status']
		);

		gen_manage_domain_search_options($tpl, $_SESSION['search_for'], $_SESSION['search_common'], $_SESSION['search_status']);
	} else {
		gen_manage_domain_query($search_query,
			$count_query,
			$admin_id,
			$start_index,
			$rows_per_page,
			'n/a',
			'n/a',
			'n/a'
		);

		gen_manage_domain_search_options($tpl, 'n/a', 'n/a', 'n/a');
	}

	$rs = execute_query($sql, $count_query);

	$records_count = $rs->fields['cnt'];

	$rs = execute_query($sql, $search_query);

	if ($records_count == 0) {
		if (isset($_SESSION['search_for'])) {
			$tpl->assign(
				array(
					'USERS_LIST'		=> '',
					'SCROLL_PREV'		=> '',
					'SCROLL_NEXT'		=> '',
					'TR_VIEW_DETAILS'	=> tr('View aliases'),
					'SHOW_DETAILS'		=> tr("Show")
				)
			);

			set_page_message(
				tr('Not found user records matching the search criteria!'),
				'notice'
			);

			unset($_SESSION['search_for']);
			unset($_SESSION['search_common']);
			unset($_SESSION['search_status']);
		} else {
			$tpl->assign(
				array(
					'USERS_LIST' => '',
					'SCROLL_PREV' => '',
					'SCROLL_NEXT' => '',
					'TR_VIEW_DETAILS' => tr('View aliases'),
					'SHOW_DETAILS' => tr("Show")
				)
			);

			set_page_message(tr('You have no users.'), 'notice');
		}
	} else {
		$prev_si = $start_index - $rows_per_page;

		if ($start_index == 0) {
			$tpl->assign('SCROLL_PREV', '');
		} else {
			$tpl->assign(
				array(
					'SCROLL_PREV_GRAY' => '',
					'PREV_PSI' => $prev_si
				)
			);
		}

		$next_si = $start_index + $rows_per_page;

		if ($next_si + 1 > $records_count) {
			$tpl->assign('SCROLL_NEXT', '');
		} else {
			$tpl->assign(
				array(
					'SCROLL_NEXT_GRAY' => '',
					'NEXT_PSI' => $next_si
				)
			);
		}

		while (!$rs->EOF) {
			if ($rs->fields['domain_status'] == $cfg->ITEM_OK_STATUS) {
				$status_icon = "ok";
			} else if ($rs->fields['domain_status'] == $cfg->ITEM_DISABLED_STATUS) {
				$status_icon = "disabled";
			} else if ($rs->fields['domain_status'] == $cfg->ITEM_ADD_STATUS
				|| $rs->fields['domain_status'] == $cfg->ITEM_CHANGE_STATUS
				|| $rs->fields['domain_status'] == $cfg->ITEM_TOENABLE_STATUS
				|| $rs->fields['domain_status'] == $cfg->ITEM_RESTORE_STATUS
				|| $rs->fields['domain_status'] == $cfg->ITEM_TODISABLED_STATUS
				|| $rs->fields['domain_status'] == $cfg->ITEM_DELETE_STATUS) {
				$status_icon = "reload";
			} else {
				$status_icon = "error";
			}
			$status_url = $rs->fields['domain_id'];

			$tpl->append(
				array(
					'STATUS_ICON' => $status_icon,
					'URL_CHANGE_STATUS' => $status_url,
				)
			);

			$admin_name = decode_idna($rs->fields['domain_name']);

			$dom_created = $rs->fields['domain_created'];

			$dom_expires = $rs->fields['domain_expires'];

			if ($dom_created == 0) {
				$dom_created = tr('N/A');
			} else {
				$dom_created = date($cfg->DATE_FORMAT, $dom_created);
			}

			if ($dom_expires == 0) {
				$dom_expires = tr('Not Set');
			} else {
				$dom_expires = date($cfg->DATE_FORMAT, $dom_expires);
			}

			$tpl->append(
				array(
					'CREATION_DATE' => $dom_created,
					'EXPIRE_DATE' => $dom_expires,
					'DOMAIN_ID' => $rs->fields['domain_id'],
					'NAME' => tohtml($admin_name),
					'USER_ID' => $rs->fields['domain_admin_id'],
					'DISK_USAGE' => ($rs->fields['domain_disk_limit'])
						? tr('%1$s of %2$s MB', round($rs->fields['domain_disk_usage'] / 1024 / 1024,1), $rs->fields['domain_disk_limit'])
						: tr('%1$s of <strong>unlimited</strong> MB', round($rs->fields['domain_disk_usage'] / 1024 / 1024,1))
				)
			);

			gen_domain_details($tpl, $sql, $rs->fields['domain_id']);
			$rs->moveNext();
		}

	}
}

function check_externel_events($tpl) {

	global $externel_event;

	if (isset($_SESSION["user_add3_added"])) {
		if ($_SESSION["user_add3_added"] === '_yes_') {
			set_page_message(tr('User added sucessfully!'), 'success');

			$externel_event = '_on_';
			unset($_SESSION["user_add3_added"]);
		}
	} else if (isset($_SESSION["edit"])) {
		if ('_yes_' === $_SESSION["edit"]) {
			set_page_message(tr('User data updated sucessfully!'), 'success');
		} else {
			set_page_message(tr('User data not updated sucessfully!'), 'error');
		}
		unset($_SESSION["edit"]);
	} else if (isset($_SESSION["user_has_domain"])) {
		if ($_SESSION["user_has_domain"] == '_yes_') {
			set_page_message(
				tr('This user has domain records!<br />First remove the domains from the system!'),
				'error'
			);
		}

		unset($_SESSION["user_has_domain"]);
	} else if (isset($_SESSION['user_deleted'])) {
		if ($_SESSION['user_deleted'] == '_yes_') {
			set_page_message(tr('User terminated!'), 'success');
		} else {
			set_page_message(tr('User not terminated!') , 'error');
		}

		unset($_SESSION['user_deleted']);
	}
}
?>