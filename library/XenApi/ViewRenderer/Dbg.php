<?php

class XenApi_ViewRenderer_Dbg extends XenForo_ViewRenderer_Abstract
{
	/**
	* Determines whether the container needs to be rendered. This may apply to an
	* entire renderer or just individual render types.
	*
	* @var boolean
	*/
	protected $_needsContainer = false;

	/**
	 * Renders the container output for a page. This often represents the "chrome" of
	 * a page, including aspects like the header and footer. The content from the other
	 * render methods will generally be put inside this.
	 *
	 * Note that not all response types will have a container. In which case, they
	 * should return the inner contents directly.
	 *
	 * @param string Contents from a previous render method
	 * @param array  Key-value pairs to manipulate the container
	 *
	 * @return string Rendered output
	 */
	public function renderContainer($contents, array $params = array())
	{}

	/**
	 * Renders output of an error.
	 *
	 * @param string Text of the error to render
	 *
	 * @return string|false Rendered output. False if rendering wasn't possible (see {@link renderUnrepresentable()}).
	 */
	public function renderError($errorText)
	{
		// TODO: Implement renderError() method.
	}

	/**
	 * Renders output of an message.
	 *
	 * @param string Text of the message to render
	 *
	 * @return string|false Rendered output. False if rendering wasn't possible (see {@link renderUnrepresentable()}).
	 */
	public function renderMessage($message)
	{
		// TODO: Implement renderMessage() method.
	}

	/**
	 * Fallback for rendering an "unrepresentable" message. Method is called when
	 * the concrete rendering function returns false or no concrete rendering function
	 * is available.
	 *
	 * @return string Rendered output
	 */
	public function renderUnrepresentable()
	{
		// TODO: Implement renderUnrepresentable() method.
	}

	/**
	 * Renders output of a view. Should instantiate the view object and render it.
	 * Note that depending on response type, this class may have to manipulate the
	 * view name or instantiate a different object.
	 *
	 * @param string Name of the view to create
	 * @param array  Key-value array of parameters for the view.
	 * @param string Name of the template that will be used to display (may be ignored by view)
	 * @param XenForo_ControllerResponse_View|null A sub-view that will be rendered internal to this view
	 *
	 * @return string|XenForo_Template_Abstract|false Rendered output. False if rendering wasn't possible (see {@link renderUnrepresentable()}).
	 */
	public function renderView($viewName, array $params = array(), $templateName = '', XenForo_ControllerResponse_View $subView = null)
	{
		return var_export($params, true);
	}
}