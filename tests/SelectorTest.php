<?php

/** 
 * @copyright Constantin GALBENU <xprt64@gmail.com>
 */
namespace Xprt64\Css;

class	SelectorTest extends \PHPUnit_Framework_TestCase
{
	protected $dom;
	
	public function __construct($name = null, array $data = array (), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		
		$this->dom	=	new \DOMDocument();
		
		$this->dom->loadHTML(<<<HTMLCODE
<html>
	<head>
		<style type="text/css">
			div.class1
			{
				color: #ff000;
			}
		</style>
		<style type="text/css">
			p.class2
			{
				text-align:center;
			}
			/* a comment the must be ignored by the parser */	
			/* the next , intentionally put here, the parser should ignore it */
			,p span
			{
				font-weight:bold;
			}
			a,
			span.link
			{
				cursor: pointer;
			}
		</style>
				
	</head>
	<body>
			<div class="class1">test1</div>
			<p class="class2 class1">test1 <span class="link">in span</span></p>
   </body>
</html>
);
HTMLCODE
);
	}
	
	public function testSelector()
	{
		$this->assertEquals( 1, Selector::selectElements("div", $this->dom)->length, "could not select 1 div element" );
		
		$this->assertEquals( 2, Selector::selectElements(".class1,.class2", $this->dom)->length, "could not select 2 elements with class1 and class2" );
	}
	
	public function testStyles()
	{
		$parser	=	new	Parser($this->dom);
		
		$parser->loadRulesFromDom();
		
		$span	=	$this->dom->getElementsByTagName('span')[0];
		
		$span_styles	=	$parser->getStylesFromCssRules($span);
		
		$this->assertCount(2, $span_styles, "span element does not have styles applied");
		
		$this->assertArrayHasKey('font-weight', $span_styles, 'style not applied (font-weight)');
	}
}
