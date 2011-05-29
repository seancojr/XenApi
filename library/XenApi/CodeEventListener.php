<?php

class XenApi_CodeEventListener
{
	/**
	 * Is the current request for the API?
	 *
	 * @var bool
	 */
	private static $_apiRequest = false;

	/**
	 * Actual API viewrenderer output
	 *
	 * @var string
	 */
	private static $_realContent = '';

	/**
	 * Singleton class, no constructor
	 */
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

	/**
	 * Fires before the view in rendered in the front controller. If this
	 * is and API request then a proper response is rendered and stored
	 * in this class staticly to be replaced later
	 *
	 * @static
	 * @param XenForo_FrontController $fc
	 * @param XenForo_ControllerResponse_Abstract $controllerResponse
	 * @param XenForo_ViewRenderer_Abstract $viewRenderer
	 * @param array $containerParams
	 * @return
	 */
	public static function FrontControllerPreView(XenForo_FrontController $fc, XenForo_ControllerResponse_Abstract &$controllerResponse,
		XenForo_ViewRenderer_Abstract &$viewRenderer, array &$containerParams)
	{
		if (!self::$_apiRequest) return;

		if (!($viewRenderer instanceof XenApi_ViewRenderer_Abstract))
		{
			// TODO: Exception?
			die('A view renderer that is not for API use was attempted to be used');
		}

		$content = $innerContent = '';
		$content = $viewRenderer->init();

		if ($controllerResponse instanceof XenApi_ControllerResponse_Data)
		{
			$innerContent = $viewRenderer->renderData($controllerResponse->data);
		}

		if ($controllerResponse instanceof XenApi_ControllerResponse_ApiError)
		{
			$errorResponse = array(
				'error' => array(
					'code' => $controllerResponse->errorCode,
					'message' => $controllerResponse->errorText
				)
			);
			$innerContent = $viewRenderer->renderData($errorResponse);
		}

		if ($innerContent != '')
		{
			$content .= $innerContent;
			$content .= $viewRenderer->close();
		}

		self::$_realContent = $content;
	}

	/**
	 * After the fake view is rendered in the FrontController, replace
	 * the content with our stored content
	 *
	 * @static
	 * @param XenForo_FrontController $fc
	 * @param  $output
	 * @return string
	 */
	public static function FrontControllerPostView(XenForo_FrontController $fc, &$output)
	{
		if (!self::$_apiRequest || self::$_realContent == '') return;

		$output .= self::$_realContent;
	}
}
