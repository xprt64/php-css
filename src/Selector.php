<?php

/** 
 * A library that selects node from a DOMDocument by a css selector
 * 
 * @link        https://github.com/xprt64/php-css
 * @copyright   Constantin GALBENU <xprt64@gmail.com>
 * @license     https://opensource.org/licenses/MIT MIT License
 */
namespace Xprt64\Css;

class	Selector
{	
	/**
	 * The DOM Document
	 * 
	 * @var \DOMDocument
	 */
	protected	$dom;
	
	/**
	 * Constructor
	 * 
	 * @param \DOMDocument $dom
	 */
	public function __construct($dom)
	{
		$this->dom	=	$dom;
	}
	
	/**
	 * Select elements that match the css selector
	 * @param string $selector
	 * @return \DOMNodeList
	 */
	public function select($selector)
	{
		return	self::selectElements($selector, $this->dom);
	}
	
	/**
	 * Returns true if node match the css selector and false otherwise
	 * 
	 * @param \DOMNode $node
	 * @param string $selector
	 * @return boolean
	 */
	public function nodeMatch($node, $selector)
	{
		$selected	=	self::selectElements($selector, $this->dom);
		
		foreach($selected as $s_node)
		{
			if($s_node === $node)
				return true;
		}
		
		return false;
	}
	
	
	/**
	 * Selects elements that match a CSS selector
	 * 
	 * Based on the implementation of TJ Holowaychuk <tj@vision-media.ca>
	 * @link https://github.com/tj/php-selector
	 * @param string $selector
	 * @param \DOMDocument $htmlOrDom
	 * @return \DOMNodeList
	 */
	public static function selectElements($selector, $htmlOrDom)
	{
		if ($htmlOrDom instanceof \DOMDocument) 
		{
			$xpath	=	new \DOMXpath($htmlOrDom);
		} 
		else 
		{
			$dom	=	new \DOMDocument();
			@$dom->loadHTML($htmlOrDom);
			$xpath	=	new \DOMXpath($dom);
		}
		
		return	$xpath->evaluate(self::selectorToXpath($selector));
	}
	/**
	 * Converts a CSS selector to a XPath expression
	 * 
	 * Based on the implementation of TJ Holowaychuk <tj@vision-media.ca>
	 * @link https://github.com/tj/php-selector
	 * @param string $selector Example: div > a.btn > span
	 * @return string
	 */
	public static function selectorToXpath($selector)
	{
		// remove spaces around operators
		$selector = preg_replace('/\s*>\s*/', '>', $selector);
		$selector = preg_replace('/\s*~\s*/', '~', $selector);
		$selector = preg_replace('/\s*\+\s*/', '+', $selector);
		$selector = preg_replace('/\s*,\s*/', ',', $selector);
		$selectors = preg_split('/\s+(?![^\[]+\])/', $selector);

		foreach ($selectors as &$selector) {
			// ,
			$selector = preg_replace('/,/', '|descendant-or-self::', $selector);
			// input:checked, :disabled, etc.
			$selector = preg_replace('/(.+)?:(checked|disabled|required|autofocus)/', '\1[@\2="\2"]', $selector);
			// input:autocomplete, :autocomplete
			$selector = preg_replace('/(.+)?:(autocomplete)/', '\1[@\2="on"]', $selector);
			// input:button, input:submit, etc.
			$selector = preg_replace('/:(text|password|checkbox|radio|button|submit|reset|file|hidden|image|datetime|datetime-local|date|month|time|week|number|range|email|url|search|tel|color)/', 'input[@type="\1"]', $selector);
			// foo[id]
			$selector = preg_replace('/(\w+)\[([_\w-]+[_\w\d-]*)\]/', '\1[@\2]', $selector);
			// [id]
			$selector = preg_replace('/\[([_\w-]+[_\w\d-]*)\]/', '*[@\1]', $selector);
			// foo[id=foo]
			$selector = preg_replace('/\[([_\w-]+[_\w\d-]*)=[\'"]?(.*?)[\'"]?\]/', '[@\1="\2"]', $selector);
			// [id=foo]
			$selector = preg_replace('/^\[/', '*[', $selector);
			// div#foo
			$selector = preg_replace('/([_\w-]+[_\w\d-]*)\#([_\w-]+[_\w\d-]*)/', '\1[@id="\2"]', $selector);
			// #foo
			$selector = preg_replace('/\#([_\w-]+[_\w\d-]*)/', '*[@id="\1"]', $selector);
			// div.foo
			$selector = preg_replace('/([_\w-]+[_\w\d-]*)\.([_\w-]+[_\w\d-]*)/', '\1[contains(concat(" ",@class," ")," \2 ")]', $selector);
			// .foo
			$selector = preg_replace('/\.([_\w-]+[_\w\d-]*)/', '*[contains(concat(" ",@class," ")," \1 ")]', $selector);
			// div:first-child
			$selector = preg_replace('/([_\w-]+[_\w\d-]*):first-child/', '*/\1[position()=1]', $selector);
			// div:last-child
			$selector = preg_replace('/([_\w-]+[_\w\d-]*):last-child/', '*/\1[position()=last()]', $selector);
			// :first-child
			$selector = str_replace(':first-child', '*/*[position()=1]', $selector);
			// :last-child
			$selector = str_replace(':last-child', '*/*[position()=last()]', $selector);
			// :nth-last-child
			$selector = preg_replace('/:nth-last-child\((\d+)\)/', '[position()=(last() - (\1 - 1))]', $selector);
			// div:nth-child
			$selector = preg_replace('/([_\w-]+[_\w\d-]*):nth-child\((\d+)\)/', '*/*[position()=\2 and self::\1]', $selector);
			// :nth-child
			$selector = preg_replace('/:nth-child\((\d+)\)/', '*/*[position()=\1]', $selector);
			// :contains(Foo)
			$selector = preg_replace('/([_\w-]+[_\w\d-]*):contains\((.*?)\)/', '\1[contains(string(.),"\2")]', $selector);
			// >
			$selector = preg_replace('/>/', '/', $selector);
			// ~
			$selector = preg_replace('/~/', '/following-sibling::', $selector);
			// +
			$selector = preg_replace('/\+([_\w-]+[_\w\d-]*)/', '/following-sibling::\1[position()=1]', $selector);
			$selector = str_replace(']*', ']', $selector);
			$selector = str_replace(']/*', ']', $selector);
		}

		// ' '
		$selector = implode('/descendant::', $selectors);
		$selector = 'descendant-or-self::' . $selector;
		// :scope
		$selector = preg_replace('/(((\|)?descendant-or-self::):scope)/', '.\3', $selector);
		// $element
		$sub_selectors = explode(',', $selector);

		foreach ($sub_selectors as $key => $sub_selector) {
			$parts = explode('$', $sub_selector);
			$sub_selector = array_shift($parts);

			if (count($parts) && preg_match_all('/((?:[^\/]*\/?\/?)|$)/', $parts[0], $matches)) {
				$results = $matches[0];
				$results[] = str_repeat('/..', count($results) - 2);
				$sub_selector .= implode('', $results);
			}

			$sub_selectors[$key] = $sub_selector;
		}

		$selector = implode(',', $sub_selectors);

		return $selector;		
	}
}