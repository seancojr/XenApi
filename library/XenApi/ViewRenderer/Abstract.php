<?php

/**
 * For tracking purposes
 */
abstract class XenApi_ViewRenderer_Abstract extends XenForo_ViewRenderer_Abstract
{
	protected $_needsContainer = false;

	protected $_isHtml = false;

	public function __construct(XenForo_Dependencies_Abstract $dependencies, Zend_Controller_Response_Http $response, Zend_Controller_Request_Http $request, $responseType)
	{
		parent::__construct($dependencies, $response, $request);

		$this->_isHtml =(substr($responseType, -2, 2) == 'fm');
	}

	protected function _format($text)
	{
		if ($this->_isHtml)
		{
			$text = $this->_formatHtml($text);
		}

		return $text;
	}

	/**
	 * Pretty-print various elements in HTML format, such as xml tags and
	 * URLs. This method also escapes characters like <
	 *
	 * This function is borrowed from the MediaWiki API
	 *
	 * @param $text string
	 * @return string
	 */
	protected function _formatHtml($text)
	{
		// Escape everything first for full coverage
		$text = htmlspecialchars( $text );

		// encode all comments or tags as safe blue strings
		$text = preg_replace( '/\&lt;(!--.*?--|.*?)\&gt;/', '<span style="color:blue;">&lt;\1&gt;</span>', $text );
		// identify URLs
		$protos = implode( "|", array('http://','https://','frp://','irc://','git://','svn://'));
		// This regex hacks around bug 13218 (&quot; included in the URL)
		$text = preg_replace( "#(($protos).*?)(&quot;)?([ \\'\"<>\n]|&lt;|&gt;|&quot;)#", '<a href="\\1">\\1</a>\\3\\4', $text );
		// identify requests to api.php
		$text = preg_replace( "#api\\.php\\?[^ \\()<\n\t]+#", '<a href="\\0">\\0</a>', $text );

		return $text;
	}

	public function init()
	{
		$mime = $this->_isHtml ? 'text/html' : $this->_getMimeType();
		$content = '';

		if ($mime)
		{
			$this->_response->setHeader('Content-Type', "$mime; charset=UTF-8", true);
		}

		if ($this->_isHtml)
		{
			$content = <<<ENDHTML
<!DOCTYPE html>
<html>
<head>
	<title>XenAPI Result</title>
</head>
<body>
<pre>
ENDHTML;
		}

		return $content;
	}

	public function close()
	{
		$content = '';

		if ($this->_isHtml)
		{
			$content = <<<ENDHTML
</pre>
</body>
</html>
ENDHTML;

		}

		return $content;
	}

	/**
	 * Renders any data
	 *
	 * @abstract
	 * @param array $data
	 * @return void
	 */
	abstract public function renderData(array $data);

	/**
	 * Returns any special mime types
	 *
	 * @abstract
	 * @return void
	 */
	abstract protected function _getMimeType();

	/*
	 * Unused in API-Land
	 */
	final public function renderUnrepresentable() {return '';}
	final public function renderView($viewName, array $params = array(), $templateName = '', XenForo_ControllerResponse_View $subView = null) {return '';}
	final public function renderContainer($contents, array $params = array()) {return '';}
}
 
