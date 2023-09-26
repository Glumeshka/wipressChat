<?php

namespace App\core;

class View
{	
	public function render($content_view, $template_view = null, $pageData = null, $payload = null)
	{
		include_once LAYOUT . $template_view;
	}
}