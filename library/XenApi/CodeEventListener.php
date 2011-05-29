<?php

class XenApi_CodeEventListener
{
	/**
	 * Is the current request for the API?
	 *
	 * @var bool
	 */
	private static $_apiRequest = false;

	private static $_realContent = '';

	private final function __construct() {}

	/**
	 * Determines if the current request is for the API
	 *
	 * @static
	 * @param XenForo_FrontController $fc
	 * @return void
	 */
	public static function FrontControllerPreRoute(XenForo_FrontController $fc)
	{
		self::$_apiRequest = ($fc->getDependencies() instanceof XenApi_Dependencies_Api);
	}

	public static function FrontControllerPreDispatch(XenForo_FrontController $fc, XenForo_RouteMatch &$routeMatch)
	{

	}

	public static function FrontControllerPreView(XenForo_FrontController $fc, XenForo_ControllerResponse_Abstract &$controllerResponse,
		XenForo_ViewRenderer_Abstract &$viewRenderer, array &$containerParams)
	{
		if (!self::$_apiRequest) return;

		if (!($viewRenderer instanceof XenApi_ViewRenderer_Abstract))
		{
			// TODO: Exception?
			die('A view renderer that is not for API use was attempted to be used');
		}

		$content = '';

		if ($controllerResponse instanceof XenApi_ControllerResponse_Data)
		{
			$content = $viewRenderer->renderData($controllerResponse->data);
		}

		self::$_realContent = $content;
	}

	public static function FrontControllerPostView(XenForo_FrontController $fc, &$output)
	{
		if (!self::$_apiRequest || self::$_realContent == '') return;

		$output .= self::$_realContent;
	}
}
