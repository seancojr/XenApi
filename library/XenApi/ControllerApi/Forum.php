<?php

class XenApi_ControllerApi_Forum extends XenApi_ControllerApi_Abstract
{
	public function actionIndex()
	{
		$parentId = $this->getParam('parent');

		if ($parentId == 0)
		{
			$parent = false;
		}
		else
		{
			// TODO: Node grabbing etc
		}

		$nodes = $this->_getNodeModel()->getNodeDataForListDisplay($parent, 0);
		$data = $this->_buildForumList($nodes['nodesGrouped'], $parentId);

		//print_r($nodes);
		return $this->responseData($data);
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
				$nodeData['children'] = $this->_buildForumList($nodesGrouped, $node['node_id']);

				if (!count($nodeData['children']))
				{
					if ($node['node_type_id'] == 'Category')
					{
						// Empty categories are pointless
						continue;
					}

					unset($nodeData['children']);
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
	 * @return XenForo_Model_Node
	 */
	protected function _getNodeModel()
	{
		return $this->getModelFromCache('XenForo_Model_Node');
	}

	protected function _getParams()
	{
		return array(
			'parent' => XenForo_Input::UINT
		);
	}
}