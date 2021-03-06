<?php
class HtmlFormatter {
	public static function format($html, $indentWith = "\t", $tagsWithoutIndentation = 'html,link,img,meta') {
		
		$html = preg_replace('/\\r?\\n([^\s])/', ' $1', $html);
		
		$html = str_replace(["\n", "\r", "\t"], ['', '', ' '], $html);
		$elements = preg_split('/(<.+>)/U', $html, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		$dom = self::parseDom($elements);
		$indent = 0;
		$output = array();
		foreach ($dom as $index => $element)
		{
			if ($element['opening'])
			{
				$output[] = "\n".str_repeat($indentWith, $indent).trim($element['content']);
				
				if ( ! in_array($element['type'], explode(',', $tagsWithoutIndentation)))
				{
					++$indent;
				}
			}
			else if ($element['standalone'])
			{
				$output[] = str_repeat($indentWith, $indent).trim($element['content']);
			}
			else if ($element['closing'])
			{
				--$indent;
				$lf = "\n".str_repeat($indentWith, abs($indent));
				if (isset($dom[$index - 1]) && $dom[$index - 1]['opening'])
				{
					$lf = '';
				}
				$output[] = $lf.trim($element['content']);
			}
			else if ($element['text'])
			{
				
				$output[] = "\n".str_repeat($indentWith, $indent >= 0 ? $indent : 0).preg_replace('/ [ \t]*/', ' ', $element['content']);
			}
			else if ($element['comment'])
			{
				$output[] = "\n".str_repeat($indentWith, $indent).trim($element['content']);
			}
		}
		return trim(implode('', $output));
	}
	public static function parseDom(Array $elements)
	{
		$dom = array();
		foreach ($elements as $element)
		{
			$isText = false;
			$isComment = false;
			$isClosing = false;
			$isOpening = false;
			$isStandalone = false;
			$currentElement = trim($element);
			
			if (strpos($currentElement, '<!') === 0)
			{
				$isComment = true;
			}
			
			else if (strpos($currentElement, '</') === 0)
			{
				$isClosing = true;
			}
			
			else if (preg_match('/\/>$/', $currentElement))
			{
				$isStandalone = true;
			}
			
			else if (strpos($currentElement, '<') === 0)
			{
				$isOpening = true;
			}
			
			else
			{
				$isText = true;
			}
			$dom[] = array(
				'text' 				=> $isText,
				'comment'			=> $isComment,
				'closing'	 		=> $isClosing,
				'opening'	 		=> $isOpening,
				'standalone'	 	=> $isStandalone,
				'content'			=> $element,
				'type'				=> preg_replace('/^<\/?(\w+)[ >].*$/U', '$1', $element)
			);
		}
		return $dom;
	}
}