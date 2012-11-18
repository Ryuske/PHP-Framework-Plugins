<?php
class Cms_Prefbits extends Cms_Variables {
	private static $prefbits = array(
		'admins' => array(
			'super admin' => '4',
			'domains admin' => '2',
			'groups admin' => '1',
		),
		'groups' => array(
			'super admin' => '4',
			'admin' => '2',
			'member' => '1'
		),
		'domains' => array(
			'access' => '1'
		),
		'keynotes' => array(
			'access' => '1'
		)
	);
	
	public function count($type) {
		if (isset($_SESSION['cms_prefbits'])) {
			$static = 3;
			$groups = Db::query('SELECT `group_name` FROM `groups`');
			$domains = Db::query('SELECT `domain_name` FROM `domains`');
			$keynotes = Db::query('SELECT `keynote_name` FROM `keynotes`');
		
			switch ($type) {
				case 'static':
					$return = $static;
				case 'groups':
					$return = count($groups);
					break;
				case 'domains':
					$return = count($domains);
					break;
				case 'keynotes':
					$return = count($keynotes);
					break;
				default:
					$return = 'Prefbits Error - Unknown Type \'' . $type . '\'';
			}
		
		return $return;
		} else {
			return 'Prefbits Error - Failure Loading';
		}
	}
	
	public function check($type, $permission, $index=0, $user=null, $is_exactly=false) {
		if (isset($_SESSION['cms_prefbits'])) {
			if ($user === null) {
				$user = $_SESSION['cms_username'];
			}
			$groups = Db::query('SELECT `group_id` FROM `groups`');
			$domains = Db::query('SELECT `domain_id` FROM `domains`');
			$keynotes = Db::query('SELECT `keynote_id` FROM `keynotes`');
			$account_prefbits = Db::query('SELECT `account_prefbits` FROM `accounts` WHERE `account_username`=:username', array('username'=>$user));
			$account_prefbits = explode('|', $account_prefbits[0]['account_prefbits']);
			$return = false;
			
			switch ($type) {
				case 'admin':
					for ($i=0; $i<count(self::$prefbits['admins']); $i++) {
						if ($account_prefbits[0] & self::$prefbits['admins'][$permission] || ($account_prefbits[0] & (self::$prefbits['admins'][$permission] << $i) && $is_exactly === false)) {
							$return = true;
						}
						if ($return === true && !($account_prefbits[0] & self::$prefbits['admins'][$permission]) && ($permission == 'groups admin' && (self::$prefbits['admins'][$permission] << $i) == 2)) {
							$return = false;
						}
					}
					break;
				case 'group':
					for ($i=0; $i<count(self::$prefbits['groups']); $i++) {
						if ($account_prefbits[1 + $index] & self::$prefbits['groups'][$permission] || ($account_prefbits[1 + $index] & (self::$prefbits['groups'][$permission] << $i)  && $is_exactly === false)) {
							$return = true;
						}	
					}
					break;
				case 'domain':
					for ($i=0; $i<count(self::$prefbits['domains']); $i++) {
						if ($account_prefbits[1 + count($groups) + $index] & self::$prefbits['domains'][$permission] || ($account_prefbits[1 + count($groups) + $index] & (self::$prefbits['domains'][$permission] << $i) && $is_exactly === false)) {
							$return = true;
						}
						if ($return === false) {
							for ($i=0; $i<count($groups); $i++) {
								if (self::check('groups', 'member', $i, $user) === true) {
									if (self::check_groups('domain', 'access', $groups[$i]['group_id'], $index) === true) {
										$return = true;
									}
								}
							}
						}
					}
					break;
				case 'keynote':
					for ($i=0; $i<count(self::$prefbits['keynotes']); $i++) {
						if ($account_prefbits[1 + count($groups) + count($domains) + $index] & self::$prefbits['keynotes'][$permission] || ($account_prefbits[1 + count($groups) + count($domains) + $index] & (self::$prefbits['keynotes'][$permission] << $i) && $is_exactly === false)) {
							$return = true;
						}
						if ($return === false) {
							for ($i=0; $i<count($groups); $i++) {
								if (self::check('groups', 'member', $i, $user) === true) {
									if (self::check_groups('keynote', 'access', $groups[$i]['group_id'], $index) === true) {
										$return = true;
									}
								}
							}
						}
					}
					break;
				default:
					$return = 'Unknown Type';
			}
			
			return $return;
		}
	}
	
	public function check_groups($type, $permission, $group, $index=0, $is_exactly=false) {
		if (isset($_SESSION['cms_prefbits'])) {
			$domains = Db::query('SELECT `domain_name` FROM `domains`');
			$keynotes = Db::query('SELECT `keynote_name` FROM `keynotes`');
			$group_prefbits = Db::query('SELECT `group_prefbits` FROM `groups` WHERE `group_id`=:id', array('id'=>$group));
			$group_prefbits = explode('|', $group_prefbits[0]['group_prefbits']);
			$return = false;
			
			switch ($type) {
				case 'domain':
					for ($i=0; $i<count(self::$prefbits['domains']); $i++) {
						if ($group_prefbits[$index] & self::$prefbits['domains'][$permission] || ($group_prefbits[$index] & (self::$prefbits['domains'][$permission] << $i) && $is_exactly === false)) {
							$return = true;
						}	
					}
					break;
				case 'keynote':
					for ($i=0; $i<count(self::$prefbits['keynotes']); $i++) {
						if ($group_prefbits[count($domains) + $index] & self::$prefbits['keynotes'][$permission] || ($group_prefbits[count($domains) + $index] & (self::$prefbits['keynotes'][$permission] << $i) && $is_exactly === false)) {
							$return = true;
						}	
					}
					break;
				default:
					$return - 'Unknown Type';
			}
			
			return $return;
		}
	}
	
	public function add($type, $prefbit=0) {
		if (isset($_SESSION['cms_prefbits'])) {
			$groups = Db::query('SELECT `group_name` FROM `groups`');
			$domains = Db::query('SELECT `domain_name` FROM `domains`');
			$keynotes = Db::query('SELECT `keynote_name` FROM `keynotes`');
			$account_prefbits = explode('|', parent::$account_info[0]['account_prefbits']);
			$new_prefbits = array();
			switch ($type) {
				case 'group':
					for ($i=0; $i<=count($groups); $i++) {
						$new_prefbits[] = $account_prefbits[$i];
					}
					$new_prefbits[] = $prefbit;
					for ($i=0, $j=count($new_prefbits); $i<count($account_prefbits) - count($new_prefbits) + 1; $i++) {
						if ($i == 0) {
							$new_prefbits1[] = '';
						}
						$new_prefbits1[] = $account_prefbits[$j - 1 + $i];
					}
					$new_prefbits = implode('|', $new_prefbits);
					if (isset($new_prefbits1)) {
						$new_prefbits .= implode('|', $new_prefbits1);
					}
					break;
				case 'domain':
					for ($i=0; $i<=count($groups) + count($domains); $i++) {
						$new_prefbits[] = $account_prefbits[$i];
					}
					$new_prefbits[] = $prefbit;
					for ($i=0, $j=count($new_prefbits); $i<count($account_prefbits) - count($new_prefbits) + 1; $i++) {
						if ($i == 0) {
							$new_prefbits1[] = '';
						}
						$new_prefbits1[] = $account_prefbits[$j - 1 + $i];
					}
					$new_prefbits = implode('|', $new_prefbits);
					if (isset($new_prefbits1)) {
						$new_prefbits .= implode('|', $new_prefbits1);
					}
					break;
				case 'keynote':
					for ($i=0; $i<=count($groups) + count($domains) + count($keynotes); $i++) {
						$new_prefbits[] = $account_prefbits[$i];
					}
					$new_prefbits[] = $prefbit;
					for ($i=0, $j=count($new_prefbits); $i<count($account_prefbits) - count($new_prefbits); $i++) {
						if ($i == 0) {
							$new_prefbits1[] = '';
						}
						$new_prefbits1[] = $account_prefbits[$j - 1 + $i];
					}
					$new_prefbits = implode('|', $new_prefbits);
					if (isset($new_prefbits1)) {
						$new_prefbits .= implode('|', $new_prefbits1);
					}
					break;
				default:
					return false;
			}
			Db::query('UPDATE `accounts` SET `account_prefbits`=:query_prefbits', array('query_prefbits' => $new_prefbits));
			return true;
		} else {
			return false;
		}
	}
	
	public function update($type, $prefbit, $index=0, $user=null) {
		if (isset($_SESSION['cms_prefbits'])) {
			if ($user === null) {
				$user = $_SESSION['cms_username'];
			}
			$groups = Db::query('SELECT `group_name` FROM `groups`');
			$domains = Db::query('SELECT `domain_name` FROM `domains`');
			$keynotes = Db::query('SELECT `keynote_name` FROM `keynotes`');
			$account_prefbits = explode('|', parent::$account_info[0]['account_prefbits']);
			$new_prefbits = array();
			
			switch ($type) {
				case 'admin':
					$account_prefbits[0] = $prefbit;
					foreach ($account_prefbits as $account_prefbit) {
						$new_prefbits[] = $account_prefbit;
					}
					$new_prefbits = implode('|', $new_prefbits);
					break;
				case 'group':
					$account_prefbits[1 + $index] = $prefbit;
					foreach ($account_prefbits as $account_prefbit) {
						$new_prefbits[] = $account_prefbit;
					}
					$new_prefbits = implode('|', $new_prefbits);
					break;
				case 'domain':
					$account_prefbits[1 + count($groups) + $index] = $prefbit;
					foreach ($account_prefbits as $account_prefbit) {
						$new_prefbits[] = $account_prefbit;
					}
					$new_prefbits = implode('|', $new_prefbits);
					break;
				case 'keynote':
					$account_prefbits[1 + count($groups) + count($domains) + $index] = $prefbit;
					foreach ($account_prefbits as $account_prefbit) {
						$new_prefbits[] = $account_prefbit;
					}
					$new_prefbits = implode('|', $new_prefbits);
					break;
				default:
					return false;
			}

			Db::query('UPDATE `accounts` SET `account_prefbits`=:query_prefbits WHERE `account_username`=:username', array('query_prefbits' => $new_prefbits, 'username' => $user));
			return true;
		} else {
			return false;
		}
	}
	
	public function remove($type, $index) {
		if (isset($_SESSION['cms_prefbits'])) {
			$groups = Db::query('SELECT `group_name` FROM `groups`');
			$domains = Db::query('SELECT `domain_name` FROM `domains`');
			$keynotes = Db::query('SELECT `keynote_name` FROM `keynotes`');
			$account_prefbits = explode('|', parent::$account_info[0]['account_prefbits']);
			$new_prefbits = array();
			
			switch ($type) {
				case 'group':
					$account_prefbits[1 + $index] = '';
					foreach ($account_prefbits as $prefbit) {
						if ($prefbit !== '') {
							$new_prefbits[] = $prefbit;
						}
					}
					$new_prefbits = implode('|', $new_prefbits);
					break;
				case 'domain':
					$account_prefbits[1 + count($groups) + $index] = '';
					foreach ($account_prefbits as $prefbit) {
						if ($prefbit !== '') {
							$new_prefbits[] = $prefbit;
						}
					}
					$new_prefbits = implode('|', $new_prefbits);
					break;
				case 'keynote':
					$account_prefbits[1 + count($groups) + count($domains) + $index] = '';
					foreach ($account_prefbits as $prefbit) {
						if ($prefbit !== '') {
							$new_prefbits[] = $prefbit;
						}
					}
					$new_prefbits = implode('|', $new_prefbits);
					break;
				default:
					return false;
			}

			Db::query('UPDATE `accounts` SET `account_prefbits`=:query_prefbits', array('query_prefbits' => $new_prefbits));
			return true;
		} else {
			return false;
		}
	}
}
