<?php
/**
 * ispCP ω (OMEGA) a Virtual Hosting Control System
 *
 * @copyright 	2001-2006 by moleSoftware GmbH
 * @copyright 	2006-2011 by ispCP | http://isp-control.net
 * @version 	SVN: $Id: settings_ports.php 3686 2010-11-27 08:08:58Z ShadowJumper $
 * @link 		http://isp-control.net
 * @author		Klaas Tammling <klaas.tammling@st-city.net>
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
 * The Original Code is "ispCP - ISP Control Panel".
 *
 * The Initial Developer of the Original Code is ispCP Team.
 * Portions created by Initial Developer are Copyright (C) 2006-2011 by
 * isp Control Panel. All Rights Reserved.
 */

require '../include/ispcp-lib.php';

check_login(__FILE__);

$cfg = ispCP_Registry::get('Config');

$tpl = ispCP_TemplateEngine::getInstance();
$template = 'ip_usage.tpl';

// static page messages
$tpl->assign(
	array(
		'TR_PAGE_TITLE'	=> tr('ispCP - Admin/IP Usage'),
		'TR_SERVER_STATISTICS' => tr('Server statistics'),
		'IP_USAGE'		=> tr('IP Usage'),
		'TR_DOMAIN_NAME'	=> tr('Domain Name'),
		'TR_RESELLER_NAME'	=> tr('Reseller Name')
	)
);

gen_admin_mainmenu($tpl, 'main_menu_statistics.tpl');
gen_admin_menu($tpl, 'menu_statistics.tpl');

gen_page_message($tpl);

listIPDomains($tpl, $sql);

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug($tpl);
}

$tpl->display($template);

unset_messages();

/**
 * Generate List of Domains assigned to IPs
 *
 * @param ispCP_TemplateEngine $tpl
 * @param ispCP_Database $sql The SQL object
 */
function listIPDomains($tpl, $sql) {

	$query = "
		SELECT
			`ip_id`, `ip_number`
		FROM
			`server_ips`;
	";

	$rs = exec_query($sql, $query);

	while (!$rs->EOF) {

		$no_domains = false;
		$no_alias_domains = false;

		$query = "
			SELECT
				`d`.`domain_name`, `a`.`admin_name`
			FROM
				`domain` d
			INNER JOIN
				`admin` a
			ON
				(`a`.`admin_id` = `d`.`domain_created_id`)
			WHERE
				`d`.`domain_ip_id` = ?
			ORDER BY
				`d`.`domain_name`;
		";

		$rs2 = exec_query($sql, $query, $rs->fields['ip_id']);
		$domain_count = $rs2->recordCount();

		if ($rs2->recordCount() == 0) {
			$no_domains = true;
		}

		while(!$rs2->EOF) {
			$tpl->assign(
				array(
					'DOMAIN_NAME'	=> $rs2->fields['domain_name'],
					'RESELLER_NAME'	=> $rs2->fields['admin_name'],
				)
			);

			$rs2->moveNext();
		}

		$query = "
			SELECT
				`da`.`alias_name`, `a`.`admin_name`
			FROM
				`domain_aliasses` da
			INNER JOIN
				`domain` d
			ON
				(`d`.`domain_id` = `da`.`domain_id`)
			INNER JOIN
				`admin` a
			ON
				(`a`.`admin_id` = `d`.`domain_created_id`)
			WHERE
				`da`.`alias_ip_id` = ?
			ORDER BY
				`da`.`alias_name`;
		";

		$rs3 = exec_query($sql, $query, $rs->fields['ip_id']);
		$alias_count = $rs3->recordCount();

		if ($rs3->recordCount() == 0) {
			$no_alias_domains = true;
		}

		while(!$rs3->EOF) {
			$tpl->assign(
				array(
					'DOMAIN_NAME'	=> $rs3->fields['alias_name'],
					'RESELLER_NAME'	=> $rs3->fields['admin_name'],
				)
			);

			$rs3->moveNext();
		}

		$tpl->assign(
			array(
				'IP'			=> $rs->fields['ip_number'],
				'RECORD_COUNT'	=> tr('Total Domains') . " : " .
					($domain_count+$alias_count),
			)
		);

		if ($no_domains && $no_alias_domains) {
			$tpl->assign(
				array(
					'DOMAIN_NAME'	=> tr("No records found"),
					'RESELLER_NAME'	=> '',
				)
			);
		}

		$tpl->assign('DOMAIN_ROW', '');
		$rs->moveNext();
	} // end while
}
?>
