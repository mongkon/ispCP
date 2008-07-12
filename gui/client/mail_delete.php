<?php
/**
 * ispCP ω (OMEGA) a Virtual Hosting Control System
 *
 * @copyright 	2001-2006 by moleSoftware GmbH
 * @copyright 	2006-2008 by ispCP | http://isp-control.net
 * @version 	SVN: $ID$
 * @link 		http://isp-control.net
 * @author 		ispCP Team
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

if (isset($_GET['id']) && $_GET['id'] !== '') {
	global $delete_id;
	$delete_id = $_GET['id'];
} else {
	user_goto('mail_accounts.php');
}

/* do we have a proper delete_id ? */
if (!isset($delete_id)) {
	header("Location: mail_accounts.php");
	die();
}

if (!is_numeric($delete_id)) {
	header("Location: mail_accounts.php");
	die();
}

$dmn_name = $_SESSION['user_logged'];

$query = <<<SQL_QUERY
        select
             t1.mail_id, t2.domain_id, t2.domain_name
        from
            mail_users as t1,
            domain as t2
        where
            t1.mail_id = ?
          and
            t1.domain_id = t2.domain_id
          and
            t2.domain_name = ?
SQL_QUERY;

$rs = exec_query($sql, $query, array($delete_id, $dmn_name));
if ($rs->RecordCount() == 0) {
	user_goto('mail_accounts.php');
}

/* check for catchall assigment !! */
$query = "select mail_acc,domain_id,sub_id,mail_type from mail_users where mail_id=?";
$res = exec_query($sql, $query, array($delete_id));
$data = $res->FetchRow();

if (preg_match(MT_NORMAL_MAIL, $data['mail_type']) || preg_match(MT_NORMAL_FORWARD, $data['mail_type'])) {
	/* mail to normal domain */
	// global $domain_name;
	$mail_name = $data['mail_acc'] . '@' . $_SESSION['user_logged']; //$domain_name;
} else if (preg_match(MT_ALIAS_MAIL, $data['mail_type']) || preg_match(MT_ALIAS_FORWARD, $data['mail_type'])) {
	/* mail to domain alias*/
	$res_tmp = exec_query($sql, "select alias_name from domain_aliasses where alias_id=?", array($data['sub_id']));
	$dat_tmp = $res_tmp->FetchRow();
	$mail_name = $data['mail_acc'] . '@' . $dat_tmp['alias_name'];
} else if (preg_match(MT_SUBDOM_MAIL, $data['mail_type']) || preg_match(MT_SUBDOM_FORWARD, $data['mail_type'])) {
	/* mail to subdomain*/
	$res_tmp = exec_query($sql, "select subdomain_name from subdomain where subdomain_id=?", array($data['sub_id']));
	$dat_tmp = $res_tmp->FetchRow();
	$mail_name = $data['mail_acc'] . '@' . $dat_tmp['subdomain_name'].'.'.$dmn_name;
}

$query = "select * from mail_users where mail_acc=?";
$res_tmp = exec_query($sql, $query, array($mail_name));
$num = $res_tmp->RowCount();
if ($num > 0) {
	$catchall_assigned = 1;
	set_page_message(tr('Please delete first CatchAll account for this email!'));
	session_register("catchall_assigned");
	header("Location: mail_accounts.php");
	die();
}

/* if we are locket wait to unlock */
check_for_lock_file();

$query = "UPDATE mail_users SET status='" . Config::get('ITEM_DELETE_STATUS') . "' WHERE mail_id = ?";
exec_query($sql, $query, array($delete_id));

send_request();
$admin_login = decode_idna($_SESSION['user_logged']);
write_log("$admin_login: deletes mail account: " . $data['mail_acc'] . "@" . $dmn_name);
$maildel = 1;
session_register("maildel");
header("Location: mail_accounts.php");
die();

?>