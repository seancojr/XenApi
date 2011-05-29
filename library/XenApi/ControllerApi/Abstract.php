<?php

abstract class XenApi_ControllerApi_Abstract extends XenForo_Controller
{
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
}