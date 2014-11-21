<?php

if ( $_SERVER['argc'] < 4 ) {
    die("Using: ".$_SERVER['argv'][0]." engine schema.xml  schema.sql schema.xml\n\n"
."engine: mysql, sqlite\n"
 );
}

function compile( $xsl_filename, $xml_filename ) {
    $xsl = new XSLTProcessor(); 
    $xsldoc = new DOMDocument(); 
    $xsldoc->load($xsl_filename); 
    $xsl->importStyleSheet($xsldoc); 

    $xmldoc = new DOMDocument(); 
    $xmldoc->load($xml_filename); 
    return $xsl->transformToXML($xmldoc);
}

$sqlcompiler = __DIR__ . "/gen" . $_SERVER["argv"][1] .".xsl";


file_put_contents( $_SERVER['argv'][3], compile( $sqlcompiler, $_SERVER['argv'][2]));
file_put_contents( $_SERVER['argv'][4], compile( __DIR__ . '/genphp.xsl', $_SERVER['argv'][2]));

echo "created SQL schema: " .$_SERVER['argv'][3] ."\n";
echo "created PHP schema: " .$_SERVER['argv'][4] ."\n";

    