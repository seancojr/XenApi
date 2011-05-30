<?php

class XenApi_ControllerApi_Forum extends XenApi_ControllerApi_Abstract
{
	public function actionIndex()
	{
		$parentId = $this->getParam('node_id');
		$data = array();

		if ($parentId == 0)
		{
			$parent = false;
		}
		else
		{
			/** @var $ftpHelper XenForo_ControllerHelper_ForumThreadPost */
			$parent = $this->_getNodeModel()->getNodeById($parentId);

			if (empty($parent['node_id'])
				|| ($parent['node_type_id'] != 'Category' && $parent['node_type_id'] != 'Forum'))
			{
				throw $this->responseException(
					$this->responseApiError('Invalid node_id given', 'invalid_node_id')
				);
			}
		}

		if (!$this->getParam('no_forums'))
		{
			$data['forums'] = $this->_getSubforums($parent, $parentId);
		}

		if (!$this->getParam('no_threads') && $parentId != 0 && $parent['node_type_id'] == 'Forum')
		{
			$data += $this->_getThreads($parent, $parentId);
		}

		return $this->responseData($data);
	}

	/**
	 * Returns subforums
	 *
	 * @param  $parent
	 * @param  $parentId
	 * @return array
	 */
	protected function _getSubforums($parent, $parentId)
	{
		$nodes = $this->_getNodeModel()->getNodeDataForListDisplay($parent, 0);

		if (!empty($nodes['nodesGrouped']))
		{
			return $this->_buildForumList($nodes['nodesGrouped'], $parentId);
		}

		return array();
	}

	/**
	 * Returns an array of threads in the forum
	 *
	 * @param  $parent
	 * @param  $parentId
	 * @return void
	 */
	protected function _getThreads($parent, $forumId)
	{
		/**	@var $ftpHelper XenForo_ControllerHelper_ForumThreadPost */
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$forum = $ftpHelper->assertForumValidAndViewable($forumId);
		$visitor = XenForo_Visitor::getInstance();

		$threadModel = $this->_getThreadModel();

		$page = max(1, $this->getParam('page'));
		$threadsPerPage = $this->getParam('per_page');

		$order = $this->getParam('order');
		$orderDirection = $this->getParam('order_direction');

		// fetch all thread info
		$threadFetchConditions = $threadModel->getPermissionBasedThreadFetchConditions($forum) + array(
			'sticky' => 0
		);
		$threadFetchOptions = array(
			'perPage' => $threadsPerPage,
			'page' => $page,

			'join' => XenForo_Model_Thread::FETCH_USER,
			'readUserId' => $visitor['user_id'],
			'postCountUserId' => $visitor['user_id'],

			'order' => $order,
			'orderDirection' => $orderDirection
		);

		$threads = $threadModel->getThreadsInForum($forumId, $threadFetchConditions, $threadFetchOptions);

		// TODO: Sticky threads

		$threadList = array();

		foreach ($threads AS $thread)
		{
			$threadList[] = $this->_cloneData($thread, array(
				'thread_id', 'node_id', 'title', 'reply_count', 'view_count',
				'user_id', 'username', 'post_date', 'discussion_open', 'last_post_date',
				'last_post_id', 'last_post_user_id', 'last_post_username'
			));
		}

		return array(
			'threads' => $threadList
		);
	}

	protected function _buildForumList(array $nodesGrouped, $parentId)
	{
		$nodes = array();

		if (!empty($nodesGrouped[$parentId]))
		{
			foreach ($nodesGrouped[$parentId] AS $key => $node)
			{
				if ($node['node_type_id'] != 'Category' && $node['node_type_id'] != 'Forum')
				{
					continue;
				}

				$nodeData = $this->_cloneData($node, array('node_id', 'title', 'description', 'discussion_count', 'message_count', 'last_post_id', 'last_post_date', 'last_post_user_id', 'last_post_username', 'last_thread_title'));
				$nodeData['forums'] = $this->_buildForumList($nodesGrouped, $node['node_id']);

				if (!count($nodeData['forums']))
				{
					if ($node['node_type_id'] == 'Category')
					{
						// Empty categories are pointless
						continue;
					}

					unset($nodeData['forums']);
				}

				$nodes[] = $nodeData;
			}
		}

		return $nodes;
	}

	/**
	 * @return XenForo_Model_Forum
	 */
	protected function _getForumModel()
	{
		return $this->getModelFromCache('XenForo_Model_Forum');
	}

	/**
	 * @return XenForo_Model_Thread
	 */
	protected function _getThreadModel()
	{
		return $this->getModelFromCache('XenForo_Model_Thread');
	}

    /**
	 * @return XenForo_Model_Node
	 */
	protected function _getNodeModel()
	{
		return $this->getModelFromCache('XenForo_Model_Node');
	}

	protected function _getParams()
	{
		return array(
			'*' => array(
				'node_id' => XenForo_Input::UINT,
				'no_forums' => false,
				'no_threads' => false,
				'page' => XenForo_Input::UINT,
				'per_page' => array(
					XenForo_Input::UINT,
					'default' => XenForo_Application::get('options')->discussionsPerPage,
					'min' => 1,
					'max' => 200
				),
				'order' => array(
					XenForo_Input::STRING,
					'default' => 'last_post_date',
					'values' => array(
						'title',
						'post_date',
						'view_count',
						'reply_count',
						'first_post_likes',
						'last_post_date'
					)
				),
				'order_direction' => array(
					XenForo_Input::STRING,
					'default' => 'desc',
					'values' => array('desc', 'asc')
				)
			)
		);
	}
}