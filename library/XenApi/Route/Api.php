<?php

/**
 * Matches API controllers
 */
class XenApi_Route_Api implements XenForo_Route_Interface
{
	private static $_modules = array(
		'forums' => 'Forums',
		'info' => 'Info'
	);

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
		list($prefix) = explode('/', $routePath);
		if ($prefix === '')
		{
			return false;
		}

		if (preg_match('#[^a-zA-Z0-9_-]#', $prefix))
		{
			return false;
		}

		if(!array_key_exists($prefix, self::$_modules))
		{
			return false;
		}

		$routeClass = 'XenApi_Route_PrefixApi_' . self::$_modules[$prefix];
		if (XenForo_Application::autoload($routeClass))
		{
			$newRoutePath = substr($routePath, strlen($prefix) + 1);
			$routeClass = XenForo_Application::resolveDynamicClass($routeClass, 'route_prefix');

			$route = new $routeClass();
			return $route->match($newRoutePath, $request, $router);
		}

		return false;
	}
}