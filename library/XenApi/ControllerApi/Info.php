<?php

class XenApi_ControllerApi_Info extends XenApi_ControllerApi_Abstract
{
	public function actionIndex()
	{
		$properties = $this->getParam('properties');
		$data = array();

		foreach($properties AS $property)
		{
			switch ($property)
			{
				case 'general':
					$data['general'] = $this->_getGeneral();
					break;
				case 'statistics':
					$data['statistics'] = $this->_getStatistics();
					break;
				case 'onlineusers':
					$data['onlineusers'] = $this->_getOnlineUsers();
					break;
			}
		}

		return $this->responseData($data);
	}

	protected function _getGeneral()
	{
		$options = XenForo_Application::get('options');
		$info = array(
			'base_url' => $options->boardUrl,
			'software' => 'XenForo',
			'version' => XenForo_Application::$version,
			'php_version' => phpversion(),
			'php_sapi' => php_sapi_name(),
			'friendly_urls' => $options->useFriendlyUrls
		);

		return $info;
	}

	protected function _getStatistics()
	{
		$boardTotals = $this->getModelFromCache('XenForo_Model_DataRegistry')->get('boardTotals');
		if (!$boardTotals)
		{
			$boardTotals = $this->getModelFromCache('XenForo_Model_Counters')->rebuildBoardTotalsCounter();
		}

		$info = $this->_cloneData($boardTotals, array('discussions', 'messages', 'users'));

		return $info;
	}

	protected function _getOnlineUsers()
	{
		$visitor = XenForo_Visitor::getInstance();

		$sessionModel = $this->getModelFromCache('XenForo_Model_Session');

		$onlineUsers = $sessionModel->getSessionActivityQuickList(
			$visitor->toArray(),
			array('cutOff' => array('>', $sessionModel->getOnlineStatusTimeout())),
			($visitor['user_id'] ? $visitor->toArray() : null)
		); //print_r($onlineUsers);

		$info = $this->_cloneData($onlineUsers, array('total', 'members', 'guests'));

		if (!$this->getParam('nouserlist'))
		{
			$userList = array();

			// TODO: Visible and followed
			foreach($onlineUsers['records'] AS $userRecord)
			{
				$user = $this->_cloneData($userRecord, array('user_id', 'username', 'last_activity', 'user_group_id'));
				$userList[] = $user;
			}

			$info['users'] = $userList;
		}

		return $info;
	}

	protected function _getParams()
	{
		return array(
			'*' => array(
				'properties' => array(
					XenForo_Input::STRING,
					'default' => 'general',
					'multi' => true,
					'values' => array(
						'general',
						'statistics',
						'onlineusers'
					)
				),
				'nouserlist' => false
			)
		);
	}
}

