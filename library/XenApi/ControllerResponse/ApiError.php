<?php

class XenApi_ControllerResponse_ApiError extends XenForo_ControllerResponse_Abstract
{
	/**
	* Text of the error that occurred
	*
	* @var string|array
	*/
	public $errorText = '';

	/**
	 * API errorcode
	 *
	 * @var string
	 */
	public $errorCode = '';
}
