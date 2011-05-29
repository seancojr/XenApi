<?php

class XenApi_Route_PrefixApi_Info implements XenForo_Route_Interface
{
	/**
	 * Method to be called when attempting to match this rule against a routing path.
	 * Should return false if no matching happened or a {@link XenForo_RouteMatch} if
	 * some level of matching happened. If no {@link XenForo_RouteMatch::$controllerName}
	 * is returned, the {@link XenForo_Router} will continue to the next rule.
	 *
	 * @param string					   Routing path
	 * @param Zend_Controller_Request_Http Request object
	 * @param XenForo_Router				  Router that routing is done within
	 *
	 * @return false|XenForo_RouteMatch
	 */
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		if ($routePath != '')
		{
			$request->setParam('properties', $routePath);
		}

		return $router->getRouteMatch('XenApi_ControllerApi_Info', false);
	}
}
