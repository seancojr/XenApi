<?php

class XenApi_Dependencies_Api extends XenForo_Dependencies_Abstract
{
	/**
	 * Determines if the controller matched by the route can be dispatched. Use this
	 * function to ensure, for example, that an admin page only shows an admin controller.
	 *
	 * @param mixed  Likely a XenForo_Controller object, but not guaranteed
	 * @param string Name of the action to call
	 *
	 * @return boolean
	 */
	public function allowControllerDispatch($controller, $action)
	{
		return ($controller instanceof XenApi_ControllerApi_Abstract);
	}

	/**
	 * Helper method to create a template object for rendering.
	 *
	 * @param string Name of the template to be used
	 * @param array  Key-value parameters to pass to the template
	 *
	 * @return XenForo_Template_Abstract
	 */
	public function createTemplateObject($templateName, array $params = array())
	{
		// TODO: Implement createTemplateObject() method.
	}

	/**
	 * Gets the name of the base view class for this type.
	 *
	 * @return string
	 */
	public function getBaseViewClassName()
	{
		// TODO: Implement getBaseViewClassName() method.
	}

	/**
	 * Gets the extra container data from template renders.
	 *
	 * @return array
	 */
	public function getExtraContainerData()
	{
		// TODO: Implement getExtraContainerData() method.
	}

	/**
	 * Gets the routing information for a not found error
	 *
	 * @return array Format: [0] => controller name, [1] => action
	 */
	public function getNotFoundErrorRoute()
	{
		// TODO: Implement getNotFoundErrorRoute() method.
	}

	/**
	 * Gets the routing information for a server error
	 *
	 * @return array Format: [0] => controller name, [1] => action
	 */
	public function getServerErrorRoute()
	{
		// TODO: Implement getServerErrorRoute() method.
	}

	/**
	 * Preloads a template with the template handler for use later.
	 *
	 * @param string Template name
	 */
	public function preloadTemplate($templateName)
	{
		// TODO: Implement preloadTemplate() method.
	}

	public function getViewRenderer(Zend_Controller_Response_Http $response, $responseType, Zend_Controller_Request_Http $request)
	{
		// TODO: This is repetitive, replace with an array?
		switch ($responseType)
		{
			case 'dbg':
			case 'dbgfm':
				return new XenApi_ViewRenderer_Dbg($this, $response, $request, $responseType);
            case 'json':
			case 'jsonfm':
				return new XenApi_ViewRenderer_Json($this, $response, $request, $responseType);
			default:
				return false;
		}
	}

	/**
	 * Routes the request.
	 *
	 * @param Zend_Controller_Request_Http $request
	 *
	 * @return XenForo_RouteMatch
	 */
	public function route(Zend_Controller_Request_Http $request)
	{
		$router = new XenForo_Router();
		$router->addRule(new XenForo_Route_ResponseSuffix(), 'ResponsePrefix')
			   ->addRule(new XenApi_Route_Api(), 'Prefix');

		return $router->match($request);
	}
}