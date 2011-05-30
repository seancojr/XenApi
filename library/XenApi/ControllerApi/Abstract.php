<?php

abstract class XenApi_ControllerApi_Abstract extends XenForo_Controller
{
	/**
	 * Cleaned parameters
	 *
	 * @var array
	 */
	private $_params = array();

	/**
	 * Warnings to be outputted
	 *
	 * @var array
	 */
	private $_warnings = array();

	/**
	 * Parameters required for the request
	 *
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
		return $this->responseApiError('You do not have permission to view this page or perform this action', 'no_permission', 403);
	}

	/**
	 * @param array $data
	 * @return XenApi_ControllerResponse_Data
	 */
	public function responseData(array $data)
	{
		if (count($this->_warnings))
		{
			$data = array_merge(array('warnings' => array_values($this->_warnings)), $data);
		}

		$controllerResponse = new XenApi_ControllerResponse_Data();
		$controllerResponse->data = $data;

		return $controllerResponse;
	}

	/**
	 * @param  $error
	 * @param  $errorCode
	 * @param int $responseCode
	 * @return XenApi_ControllerResponse_ApiError
	 */
	public function responseApiError($error, $errorCode, $responseCode = 200)
	{
		$controllerResponse = new XenApi_ControllerResponse_ApiError();
		$controllerResponse->errorText = $error;
		$controllerResponse->errorCode = $errorCode;
		$controllerResponse->responseCode = $responseCode;

		return $controllerResponse;
	}

	/**
	 * Disable CSRF checking, messes with json output
	 *
	 * @param  $action
	 * @return
	 */
	protected function _checkCsrf($action)
	{
		return;
	}

	/**
	 * Method designed to be overridden by child classes to add pre-dispatch
	 * behaviors. This differs from {@link _preDispatch()} in that it is designed
	 * for abstract controller type classes to override. Specific controllers
	 * should override preDispatch instead.
	 *
	 * @param string $action Action that is requested
	 */
	protected function _preDispatchType($action)
	{
		$this->_assertBoardActive($action);
		$this->_handleParams($action);
	}

	/**
	 * Disable sessions in the API
	 *
	 * @param  $action
	 * @return void
	 */
	protected function _setupSession($action) {}

	/**
	 * Returns a cleaned API parameter
	 *
	 * @param  $paramName
	 * @return array|null
	 */
	protected function getParam($paramName)
	{
		if (isset($this->_params[$paramName]))
		{
			return $this->_params[$paramName];
		}

		return null;
	}

	/**
	 * Cleans any parameters for use in the request
	 *
	 * @throws XenForo_ControllerResponse_Exception
	 * @return
	 */
	private function _handleParams($currentAction)
	{
		$cleanedParams = $params = array();
		$paramsList = $this->_getParams();

		if ($paramsList === false)
		{
			return;
		}

		foreach ($paramsList AS $action => $actionParams)
		{
			if ($action == '*' || $action == $currentAction)
			{
				$params += $actionParams;
			}
		}

		foreach ($params AS $paramName => $paramOptions)
		{
			if ($paramOptions === false)
			{
				$cleanedParams[$paramName] = ($this->_request->getParam($paramName) !== null);
				unset($params[$paramName]);
				continue;
			}

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

		$cleanedParams += $this->_input->filter($params);

		foreach ($params AS $paramName => $paramOptions)
		{
			if (!is_array($paramOptions))
			{
				continue;
			}

			$value = $cleanedParams[$paramName];

			if (isset($paramOptions['multi']) || isset($paramOptions['values']))
			{
				$value = $this->_handleMultiParam($paramName, $paramOptions, $value);
			}

			if (isset($paramOptions['min']) || isset($paramOptions['max']))
			{
				$value = $this->_handleRangeParam($paramName, $paramOptions, $value);
			}

			$cleanedParams[$paramName] = $value;
		}

		$this->_params = $cleanedParams;
	}

	/**
	 * Returns an array of values given in 'a|b|c' notation
	 *
	 * @throws XenForo_ControllerResponse_Exception
	 * @param  $paramName
	 * @param  $paramOptions
	 * @param  $cleanedParams
	 * @return array
	 */
	private function _handleMultiParam($paramName, $paramOptions, $value)
	{
		$values = explode('|', $value);
		$allowedValues = isset($paramOptions['values']) ? $paramOptions['values'] : null;
		$allowMultiple = isset($paramOptions['multi']) ? $paramOptions['multi'] : false;

		if (!$allowMultiple && count($values) != 1)
		{
			$possibleValues = is_array($allowedValues) ? "of '" . implode("', '", $allowedValues) . "'" : '';
			throw $this->responseException(
				$this->responseError("Only one $possibleValues is allowed for parameter '$paramName'")
			);
		}

		if (is_array($allowedValues))
		{
			$unknown = array_diff($values, $allowedValues);
			if (count($unknown))
			{
				if ($allowMultiple)
				{
					$s = count($unknown) > 1 ? 's' : '';
					$vals = implode(', ', $unknown);
					$this->_addWarning( "Unrecognized value$s for parameter '$paramName': $vals" );
				}
				else
				{
					throw $this->responseException(
						$this->responseError("Unrecognized value for parameter '$paramName': {$values[0]}")
					);
				}

				// Remove the bad entries
				$values = array_intersect($values, $allowedValues);
			}
		}

		return ($allowMultiple) ? $values : $values[0];
	}

	/**
	 * Verifies that integers are in range
	 *
	 * @param  $paramName
	 * @param  $paramOptions
	 * @param  $value
	 * @return
	 */
	private function _handleRangeParam($paramName, $paramOptions, $value)
	{
		$filterType = $paramOptions[0];

		if ($filterType != XenForo_Input::INT && $filterType != XenForo_Input::UINT)
		{
			throw new XenForo_Exception("Invalid filter type mixed with a range limit (param $paramName)");
		}

		$min = isset($paramOptions['min']) ? $paramOptions['min'] : null;
		$max = isset($paramOptions['max']) ? $paramOptions['max'] : null;

		if (is_array($value))
		{
			foreach ($value AS &$v)
			{
				$this->_validateLimit($paramName, $v, $min, $max);
			}
		}
		else
		{
			$this->_validateLimit($paramName, $value, $min, $max);
		}

		return $value;
	}

	/**
	 * Helper function for _handleRangeParam
	 *
	 * @param  $paramName
	 * @param  $value
	 * @param  $min
	 * @param  $max
	 * @return void
	 */
	private function _validateLimit($paramName, &$value, $min, $max)
	{
		if ($min !== null && $value < $min)
		{
			$this->_addWarning("$paramName may not be less than $min (set to $value)");
			$value = $min;
		}

		if ($max !== null && $value > $max)
		{
			$this->_addWarning("$paramName may not be over $max (set to $value)");
			$value = $max;
		}
	}

	/**
	 * Checks that the board is currently active (and can be viewed by the visitor)
	 * or throws an exception.
	 *
	 * @param string $action
	 */
	protected function _assertBoardActive($action)
	{
		$options = XenForo_Application::get('options');
		if (!$options->boardActive && !XenForo_Visitor::getInstance()->get('is_admin'))
		{
			throw $this->responseException($this->responseApiError($options->boardInactiveMessage, 'forum_closed'), 503);
		}
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

	/**
	 * Adds a warning to be displayed during output
	 *
	 * @param  $warning
	 * @return void
	 */
	protected function _addWarning($warning)
	{
		if (!in_array($warning, $this->_warnings))
		{
			$this->_warnings[] = $warning;
		}
	}
}