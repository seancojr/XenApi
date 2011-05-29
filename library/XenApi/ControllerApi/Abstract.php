<?php

abstract class XenApi_ControllerApi_Abstract extends XenForo_Controller
{
	private $_params = array();

	/**
	 * Parameters required for the request
	 *
	 * @abstract
	 * @return void
	 */
	protected function _getParams()
	{
		return false;
	}

	/**
	 * Gets the response for a generic no permission page.
	 *
	 * @return XenForo_ControllerResponse_Error
	 */
	public function responseNoPermission()
	{
		// TODO: Implement responseNoPermission() method.
	}

	/**
	 * @param array $data
	 * @return XenApi_ControllerResponse_Data
	 */
	public function responseData(array $data)
	{
		$controllerResponse = new XenApi_ControllerResponse_Data();
		$controllerResponse->data = $data;

		return $controllerResponse;
	}

	protected function _checkCsrf($action)
	{
		return;
	}

	protected function _preDispatchType($action)
	{
		$this->_handleParams();
	}

	protected function getParam($paramName)
	{
		if (isset($this->_params[$paramName]))
		{
			return $this->_params[$paramName];
		}

		return null;
	}

	private function _handleParams()
	{
		$params = $this->_getParams();

		if ($params === false)
		{
			return;
		}

		foreach ($params AS $paramName => $paramOptions)
		{
			if (!is_array($paramOptions))
			{
				continue;
			}

			$data = $this->_request->getParam($paramName);

			if (isset($paramOptions['required']) && $data === null)
			{
				throw $this->responseException(
					$this->responseError("Missing required parameter $paramName")
				);
			}
		}

		$this->_params = $this->_input->filter($params);
	}

	/**
	 * Clones data from one array to another
	 *
	 * @param array $source
	 * @param array $keys
	 */
	protected function _cloneData(array $source, array $keys)
	{
		$target = array();

		foreach ($keys AS $key)
		{
			if (isset($source[$key]))
			{
				$target[$key] = $source[$key];
			}
		}

		return $target;
	}
}