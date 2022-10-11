<?php
namespace models;

class Options extends Main 
{
	
	public function scanBGFolder()
    {
		$result = array();
		
		for( $i = 0; $i < 12; $i++ )
		{
			$result[$i]['body'] = "bodyimg".($i);
			$result[$i]['prev'] = "bodyimgPrev".($i);
			$result[$i]['checked'] = '';
		}
		return $result;
	}

}