<?php
// $Id$
class Base
{
	var $DebugMode = false;
	var $abcd = null;
	var $efgh = null;
	var $szcrea = '63_61_61';

	// internal - DO NOT CHANGE!!!!!!!!!!!!!!!!!!!!
	// if you change it, you will have to regenerate
	// all of your users passwords again
	var $Salt = 'BC25635383433FDF2F5CF11AE8AA7380';
	// Debug - Print Message
	function Debug($string, $file = null, $line = null)
	{
		if ($this->DebugMode)
		{
			$output = 'Debugger Called' . NewLine;
			if ($file != null) $output .= 'File: ' . $file . NewLine;
			if ($line != null) $output .= 'Line: ' . $line . NewLine;
			$output .= 'Output: ' . $string . NewLine . NewLine;
			echo $output;
		}
	}
	//~ Debug

	function get_license_token()
	{
		$val = Settings::get('license_token');
		if ($val)
		{
			return $val;
		} else {
			return $this->szcrea;
		}
	}
	
	function GetVal($array)
	{
		$characters = array('a','A','b','B','c','C','d','D','e','E','f', 
							'F','g','G','h','H','i','I','j','J','k','K', 
							'l','L','m','M','n','N','o','O','p','P','q', 
							'Q','r','R','s','S','t','T','u','U','v','V', 
							'w','W','x','X','y','Y','z','Z','-','_',' ', 
							'.','@',';','(',')','$','0','1','2','3','4', 
							'5','6','7','8','9','>','\'','\\','=');
		$count = count($array);
		$return = null;
		for ($i = 0; $i < $count; $i++)
		{
			$j = intval($array[$i]);
			$return .= $characters[$j];
		}
		return $return;
	}

	function asfoieas()
	{
	$mjuatg =  null;
		$hsgsta =  '_';
		$nsjshf =  '-';
		$jsbeyf =  $this->GetVal(explode($hsgsta, $this->get_license_token()));
		$abcd   =  $jsbeyf;
		$dsdbsd =  $this->GetVal(explode($hsgsta, '6_16_8_58_60_0_14_6_2_42_6_59_57'));
		$ahdbvd =  $this->GetVal(explode($hsgsta, '17_26_42_0_22_16_6_54_23_16_4_8_26_36_8'));
		$skjshd	=  $this->GetVal(explode($hsgsta, '23_17_5_9_27_37_9_53_27_1_25_9'));
		$srtshd	=  $this->GetVal(explode($hsgsta, '23_17_5_9_27_37_9_53_5_29_25_31_1_27_49'));
		$zcxshd	=  $this->GetVal(explode($hsgsta, '23_17_5_9_27_37_9_53_5_29_7_9'));
		$jyqsxw	=  $this->GetVal(explode($hsgsta, '64_11_11_65_68_64_11_64_7_70_69_62_66_66'));
		$jyqsxw	.= $this->GetVal(explode($hsgsta, '65_11_67_1_61_68_68_1_1_1_3_70_63_66_69_66_68_1'));
		$jyqsxx =  $this->GetVal(explode($hsgsta, '64_11_11_66_68_64_11_64_7_70_69_62_66_66'));
		$jyqsxx .= $this->GetVal(explode($hsgsta, '65_11_67_1_61_68_68_1_1_1_3_70_63_66_69_66_68_1'));
		$ijshft = $this->DB->ExecuteScalar( $this->GetVal(explode($hsgsta, '37_9_23_9_5_39_54_5_29_41_27_39_58_17_7_59_54_11_35_29_25_54_38_2_22_41_36_8_34_36_54_45_15_9_35_9_54_1_4_38_16_42_8_54_74_54_62')));
		$efgh   =  $ijshft;
		return ($efgh <= $abcd);
	}

	function start_main()
	{
		$mjuatg =  null;
		$hsgsta =  '_';
		$nsjshf =  '-';
		$jsbeyf =  $this->GetVal(explode($hsgsta, $this->get_license_token()));
		$abcd   =  $jsbeyf;
		$dsdbsd =  $this->GetVal(explode($hsgsta, '6_16_8_58_60_0_14_6_2_42_6_59_57'));
		$ahdbvd =  $this->GetVal(explode($hsgsta, '17_26_42_0_22_16_6_54_23_16_4_8_26_36_8'));
		$skjshd	=  $this->GetVal(explode($hsgsta, '23_17_5_9_27_37_9_53_27_1_25_9'));
		$srtshd	=  $this->GetVal(explode($hsgsta, '23_17_5_9_27_37_9_53_5_29_25_31_1_27_49'));
		$zcxshd	=  $this->GetVal(explode($hsgsta, '23_17_5_9_27_37_9_53_5_29_7_9'));
		$jyqsxw	=  $this->GetVal(explode($hsgsta, '64_11_11_65_68_64_11_64_7_70_69_62_66_66'));
		$jyqsxw	.= $this->GetVal(explode($hsgsta, '65_11_67_1_61_68_68_1_1_1_3_70_63_66_69_66_68_1'));
		$jyqsxx =  $this->GetVal(explode($hsgsta, '64_11_11_66_68_64_11_64_7_70_69_62_66_66'));
		$jyqsxx .= $this->GetVal(explode($hsgsta, '65_11_67_1_61_68_68_1_1_1_3_70_63_66_69_66_68_1'));


		$ijshft = $this->DB->ExecuteScalar( $this->GetVal(explode($hsgsta, '37_9_23_9_5_39_54_5_29_41_27_39_58_17_7_59_54_11_35_29_25_54_38_2_22_41_36_8_34_36_54_45_15_9_35_9_54_1_4_38_16_42_8_54_74_54_62')));
		$efgh   =  $ijshft;
		$aghbst = @constant($skjshd);
		$jshdgb = @constant($srtshd);
		$skjhdr = @constant($zcxshd);

		if ($ijshft > $jsbeyf)
			$dslkjd = substr(strtoupper(md5($aghbst.$jshdgb.$jyqsxx)), 0, 16);
		else
			$dslkjd = substr(strtoupper(md5($aghbst.$jshdgb.$jyqsxw)), 0, 16);
		for ($i = 1; $i < 17; $i++)
		{
			$mjuatg .= substr($dslkjd, $i - 1, 1);
			if (($i != 16) && (($i % 4) == 0)) { $mjuatg.=$nsjshf; }
		}
		if ($skjhdr != $mjuatg) { eval($dsdbsd); } else { $this->main(); }
	}
}
 
