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
		// TODO: Implement responseNoPermission() method.
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
		$this->_handleParams();
	}

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

		$cleanedParams = $this->_input->filter($params);

		foreach ($params AS $paramName => $paramOptions)
		{
			if (!is_array($paramOptions))
			{
				continue;
			}

			if (isset($paramOptions['multi']) || isset($paramOptions['values']))
			{
				$values = explode('|', $cleanedParams[$paramName]);
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

				$cleanedParams[$paramName] = ($allowMultiple) ? $values : $values[0];
			}
		}

		$this->_params = $cleanedParams;
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