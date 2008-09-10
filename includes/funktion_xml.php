<?PHP


class element
{
	var $name = '';
	var $attributes = array();
	var $data = '';
	var $depth = 0;
	var $sub = array();
}

$XMLDEBUG = 0;
$depth = 0;
$XMLmain = new element;
$XMLpos = array( 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
//$XMLpos = array( 0, 0, 0, 0, 0, 0);
   
function convertValues( $Data)
{
	global $XMLDEBUG;
	if( $XMLDEBUG)
	{
		$Data = htmlspecialchars($Data);
		$Data = mysql_escape_string($Data);
		$Data = htmlentities($Data);
	}
	$Data = utf8_decode($Data);
	return $Data;
}


function dataXMLmain( $Data, &$Objekt, $Tiefe )
{
	global $XMLmain, $XMLpos, $depth, $XMLDEBUG;
	
	if( $XMLDEBUG)	
	  echo "?$Tiefe$depth";
	if( ($depth-1)==$Tiefe)
	{	
//		$Objekt->sub[ $XMLpos[$Tiefe] ]->data .= convertValues($Data);
		$Objekt->sub[ $XMLpos[$Tiefe] ]->data .= htmlentities( convertValues($Data), ENT_QUOTES);
		 
		if( $XMLDEBUG)
		  echo "???". $Objekt->sub[ $XMLpos[$Tiefe] ]->name. "|$Data|$Tiefe???<br>";
	}
	else
		dataXMLmain( $Data, $Objekt->sub[ $XMLpos[$Tiefe] ], $Tiefe+1);
}

function startXMLmain( $Data, &$Objekt, $Tiefe )
{
	global $XMLpos, $depth, $XMLDEBUG;

	if( $XMLDEBUG)
	 if($Tiefe==1) 
	 { print_r(array_values ($XMLpos)); echo "--".$Data->name;
	   echo " #$Tiefe/$depth#";
	 }
	
	if( $depth==$Tiefe)
	{
		$Objekt->sub[ $XMLpos[$Tiefe] ] = $Data;
		if( $XMLDEBUG)
		  echo "|". $XMLpos[$Tiefe]."|". $Objekt->sub[ $XMLpos[$Tiefe] ]->name. " ". $Data->name." save|". "#-#<br>";
	}
	else
		startXMLmain( $Data, $Objekt->sub[ $XMLpos[$Tiefe] ], $Tiefe+1);
}

function start_element_handler($parser, $name, $attribs)
{
	global $depth, $XMLmain, $XMLpos;

	$Data = new element;
	$Data->name = $name;
	while(list($key, $value) = each($attribs))
		$Data->attributes[$key] = convertValues($value);
	$Data->depth = $depth;
 	$XMLpos[$depth]++;

	if( $depth==0)
		$XMLmain= $Data;
	else
		startXMLmain( $Data, $XMLmain, 1);

	$depth++;
}

function end_element_handler($parser, $name)
{
	global $depth, $XMLpos;
	$XMLpos[$depth]=0;
	$depth--;
}

function character_data_handler($parser, $data)
{
	global $XMLmain;
	if( strlen(trim($data)) )
		dataXMLmain( $data, $XMLmain, 1);
}

/*#######################################################################################*/
function readXMLfile( $file ) 
{
	global $XMLDEBUG;
	
	//$xml_parser = xml_parser_create_ns();
	$xml_parser = xml_parser_create("UTF-8");
	xml_set_element_handler($xml_parser, "start_element_handler", "end_element_handler");
	xml_set_character_data_handler($xml_parser, "character_data_handler");
	
	if (file_exists($file)) 
	{
		if (!($fp = fopen($file, "r")))
		{
			echo(" <h1>could not open XML file \"$file\"</h1>");
			return -1;
		}
	}
	else
	{
		echo(" <h1>XML file \"$file\" not exist</h1>");
		return -1;
	}

	if( $XMLDEBUG)	echo "<pre>";
	while ($data = fread($fp, 4096)) 
	{
		if (!xml_parse($xml_parser, $data, feof($fp))) 
		{
			die(sprintf("XML error: %s at line %d",
				    xml_error_string(xml_get_error_code($xml_parser)),
				    xml_get_current_line_number($xml_parser)));
		}
	}
	if( $XMLDEBUG)	echo "</pre>";
	xml_parser_free($xml_parser);
	return 0;
}

/*#######################################################################################*/
function getXMLsubPease( $Sourse, $Name ) 
{
	while(list($key, $value) = each($Sourse->sub))
		if( $value->name == $Name)
			return $value;
	
	echo "<h1>Fehler: getXMLsubPease( $Sourse, $Name ) not found</h1>";
//	die;
}

/*#######################################################################################*/
function getXMLsubData( $Sourse, $Name ) 
{
	$XML = getXMLsubPease( $Sourse, $Name);
	return $XML->data;
}
?>
