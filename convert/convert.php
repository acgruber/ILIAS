<?php

/**
* ILIAS 2 to ILIAS 3 content converter
*
* @author	Matthias Rulinski <matthias.rulinski@mi.uni-koeln.de>
* @version	$Id$
*/

/**
* ILIAS 2 to ILIAS 3 converter
* @include
*/
require_once "class.ILIAS2To3Converter.php";

// submit activated -> execute converter
if ($_REQUEST["ok"] == "ok")
{
	// DB connection data
	$user = $_REQUEST["user"];
	$pass = $_REQUEST["pass"];
	$host = $_REQUEST["host"];
	$dbname = $_REQUEST["dbname"];
	
	// zip command, id and inst of the Learning Unit, ILIAS 2 directory, source directory, target directory
	$zipCmd = $_REQUEST["zipCmd"];
	$luId = (integer) $_REQUEST["luId"];
	$luInst = (integer) $_REQUEST["luInst"];
	$iliasDir = $_REQUEST["iliasDir"];
	$sDir = $_REQUEST["sDir"];
	$tDir = $_REQUEST["tDir"];

	// check if set
	if (is_string($zipCmd) and
		is_integer($luId) and
		is_integer($luInst) and
		is_string($iliasDir) and
		is_string($sDir) and
		is_string($tDir))
	{
		// initialize object
		$exp = new ILIAS2To3Converter($user, $pass, $host, $dbname, $zipCmd, $iliasDir, $sDir, $tDir);
		// status message
		echo "Export of ILIAS 2 Learning Unit ".$luId." from installation ".$luInst." to an ILIAS 3 Learning Module";
		echo "<br>--------------------------------------------------<br>&nbsp;Export process started (do not interrupt, please)<br>&nbsp;";
		flush();
		// convert
		$exp->dumpLearningModuleFile($luId, $luInst, TRUE);
		// status message
		echo "Export process finished<br>--------------------------------------------------";
		flush();
		// destroy object
		$exp->_ILIAS2To3Converter();
	}
	else
	{
		die ("ERROR: Fill all fields, please.");
	}
}
else // display form
{
	echo "<html>\n".
			"<head>\n".
				"<title>ILIAS 2 to ILIAS 3 converter (experimental)</title>\n".
			"</head>\n".
			"<body>\n".
				"<b>Converting ILIAS 2 Learning Units to ILIAS 3 Learning Modules (experimental)</b><br /><br />\n".
				"<form action=\"".$_SERVER["PHP_SELF"]."\" method=\"post\" enctype=\"multipart/form-data\">\n".
					"ILIAS 2 databaseconnection:<br /><br />\n".
					"user:<br /><input type=\"text\" name=\"user\" maxlengh=\"30\" size=\"20\" value=\"mysql\"><br />\n".
					"pass:<br /><input type=\"password\" name=\"pass\" maxlengh=\"30\" size=\"20\" value=\"\"><br />\n".
					"host:<br /><input type=\"text\" name=\"host\" maxlengh=\"30\" size=\"20\" value=\"localhost\"><br />\n".
					"dbname:<br /><input type=\"text\" name=\"dbname\" maxlengh=\"30\" size=\"20\" value=\"virtus\"><br /><br />\n".
					"Zip command:<br /><br />\n".
					"<input type=\"text\" name=\"zipCmd\" maxlengh=\"50\" size=\"40\" value=\"c:/zip/zip\"><br /><br />\n".
					"Learning Unit:<br /><br />\n".
					"Id:<br /><input type=\"text\" name=\"luId\" maxlengh=\"10\" size=\"10\" value=\"5\"><br />\n".
					"Installation Id:<br /><input type=\"text\" name=\"luInst\" maxlengh=\"10\" size=\"10\" value=\"101\"><br /><br />\n".
					"Full path to the ILIAS 2 base directory:<br /><br />\n".
					"<input type=\"text\" name=\"iliasDir\" maxlengh=\"50\" size=\"40\" value=\"d:/htdocs/ilias/\"><br /><br />\n".
					"Full path to the source directory containing the raw data files:<br /><br />\n".
					"<input type=\"text\" name=\"sDir\" maxlengh=\"50\" size=\"40\" value=\"d:/ILIAS/SQL/\"><br /><br />\n".
					"Full path to the target directory:<br /><br />\n".
					"<input type=\"text\" name=\"tDir\" maxlengh=\"50\" size=\"40\" value=\"c:/Temp/\"><br /><br />\n".
					"<input type=\"submit\" name=\"ok\" value=\"ok\">\n".
				"</form>\n".
			"</body>\n".
		"</html>\n";
}

?>
