<?php
class cms_variables {
	protected static $account_info;

	public function Variables() {
		Db::getInstance();

		if (isset($_SESSION['cms_username'])) {
			self::$account_info = Db::query('SELECT `account_prefbits` FROM `accounts` WHERE `account_username`=:username', array('username' => $_SESSION['cms_username']));
			$_SESSION['cms_prefbits'] = new cms_prefbits;
		} else {
			self::$account_info = Db::query('SELECT `account_prefbits` FROM `accounts` LIMIT 0,1');
			$_SESSION['cms_prefbits'] = new cms_prefbits;
		}
	}
}
