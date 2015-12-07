<?php

/** 
 * @copyright Constantin GALBENU <xprt64@gmail.com>
 */
namespace Xprt64\Css;

class	ParserTest extends  \PHPUnit_Framework_TestCase
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
			p span
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
			<p class="class1">test1 <span class="link">in span</span></p>
   </body>
</html>
);
HTMLCODE
);
}
	
	public function testLoadRules()
	{
		$parser	=	new	Parser($this->dom);
		
		$parser->loadRulesFromDom();
		
		$rules	=	$parser->getRules();
		
		//var_dump($rules);
		
		$this->assertNotEmpty($rules, 'no rules found');
		
		$this->assertCount(5, $rules, 'the number of rules is not 5');
		
		$this->assertArrayHasKey('selector', $rules[0], "rule #0 does not have a selector key");
	}
}

