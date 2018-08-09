<?php
// PRUEBA PARA GUARDAR EN UN NODO DE UN XML
// $archivoXml = "archivoXml.xml";
// $xmlDoc = new DOMDocument();
// $xmlDoc->load($archivoXml);

// //Cambiar Fecha a actual y guardar en archivo
// $xmlDoc->firstChild->setAttribute('Fecha', '15 Febrero 95');
// $xmlString = $xmlDoc->saveXML();
// file_put_contents($archivoXml, $xmlString);
$xml = simplexml_load_file("archivoXml.xml");
$v_tipos = array();
foreach ($xml->Comprobante as $nodo){
	$v_tipos [] = $v_tipos [] = $nodo['Certificado'];
	echo $nodo['Certificado'];
}
//echo $v_tipos;
?>