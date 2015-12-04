<?php
/**
 * A PHP library that parses CSS
 *
 * @link        https://github.com/xprt64/php-css
 * @copyright   Constantin GALBENU <xprt64@gmail.com>
 * @license     https://opensource.org/licenses/MIT MIT License
 */

namespace Xprt64\Css;

/**
 * Common Css functions
 */
class Parser
{
	/**
	 * The DOM Document 
	 * @var \DOMDocument
	 */
	protected	$dom;
	
	/**
	 * The css rules
	 * 
	 * @var array
	 */
	protected	$rules	=	array();

	/**
	 * 
	 * @param \DOMDocument $dom
	 */
	public function __construct($dom)
	{
		$this->dom	=	$dom;
	}

	public function loadRulesFromDom()
	{
		$styles	=	$this->dom->getElementsByTagName('style');
		
		foreach($styles as $style_node)
		{
			$this->rules	=	array_merge($this->rules, self::parse($style_node->nodeValue));
		}
	}
	
	/**
	 * Getter for $this->rules
	 * 
	 * @return array
	 */
	public function getRules()
	{
		return	$this->rules;
	}
	
	/**
	 * Returns an array with styles that apply to the node
	 * based on the $this->rules
	 * 
	 * @todo prioritize the rules according to w3c specifications
	 * 
	 * @param \DOMNode $node
	 * @return array
	 */
	public	function getStylesFromCssRules($node)
	{
		$css_selector = new Selector($node->ownerDocument);
	
		$css	=	array();
		
		foreach($this->rules as $rule)
		{
			$selector	=	$rule['selector'];
			
			if($css_selector->nodeMatch($node, $selector))
			{
				foreach($rule['style'] as $style)
				{
					list($k, $v)	=	explode(':', $style);
					
					$v	=	trim($v, "\r\n\t ");
					$k	=	trim($k, "\r\n\t ");
					if($k && $v !== '')
						$css[$k]	=	trim($v, "\r\n\t ");
				}
				
			}
		}
		
		return	$css;
	}
	
	/**
	 * Parse and returns the rules in a css text
	 * @param string $text
	 * @return array
	 */
	public static function parse($text)
	{
		$styles	=	array();
		
		$length	=	strlen($text);
		
		$offset	=	-1;
		
		$selector	=	'';
		
		$in_selector=	true;
		$style	=	'';
		
		while(++$offset < $length)
		{
			$c	=	$text[$offset];
			
			if($in_selector)
			{
				if('{' == $c)
				{
					$style	=	'';
					$in_selector	=	false;
					continue;
				}
				
				$selector	.=	$c;
			}
			else
			{
				if('}' == $c)
				{
					$styles[]	=	array(
						'selector'	=>	trim($selector, "\r\n\t "),
						'style'		=>	$style,
					);
					$in_selector	=	true;
					$selector	=	'';
					continue;
				}
				else
					$style	.=	$c;
			}
		}
		
		return	self::expandSelectors(self::expandStyles($styles));
	}
	
	/**
	 * Explodes $rules[]['style'] by ; and then by :
	 * @param array $rules
	 * @return array
	 */
	public static function expandStyles($rules)
	{
		foreach($rules as &$rule)
		{
			$style	=	$rule['style'];
			
			$styles	=	explode(';', $style);
			$styles	=	array_map(function($style){
				return trim($style, "\r\n\t ,");
			}, $styles);
				
			$styles	=	array_filter($styles, function($s){
				return !empty($s);
			});
	
			$rule['style']	=	$styles;
		}
		
		unset($rule);
		
		return	$rules;
	}
	
	/**
	 * Expand the selectors by ',' and then copy the styles to every selector
	 * 
	 * div, p {text-align:center} goes to div {text-align:center} and p {text-align:center}
	 * 
	 * @param array $rules
	 * @return array
	 */
	public static function expandSelectors($rules)
	{
		$ret	=	array();
		
		foreach($rules as $rule)
		{
			$selectors	=	explode(',', $rule['selector']);
			
			$selectors	=	array_map(function($s){
				return trim($s, "\r\n\t ,");
			}, $selectors);
			
			$selectors	=	array_filter($selectors, function($s){
				return !empty($s);
			});
			
			foreach($selectors as $s)
			{
				$ret[]	=	array(
					'selector'	=>	$s,
					'style'	=>	$rule['style'],
				);
			}
		}
		
		return	$ret;
	}
}
