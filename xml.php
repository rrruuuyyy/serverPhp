<?php

// Configuración de cabeceras
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token");
header('content-type: application/json; charset=utf-8');
$method = $_SERVER['REQUEST_METHOD'];

if($method == "OPTIONS") {
    die();
}

require 'vendor/autoload.php';

$postdata = file_get_contents("php://input");
    $data = json_decode($postdata,true);

    $lugarExpedicion = $data['lugarExpedicion'];
    $metodoPago = $data['metodoPago'];
    $tipoDeComprobante = $data['tipoDeComprobante'];
    $total = $data['total'];
    $moneda = $data['moneda'];
    $descuento = $data['descuento'];
    $subTotal = $data['subtotal'];  
    $condicionesDePago = $data['condicionesDePago'];
    $formaPago = $data['formaPago'];
    $folio = $data['folio'];
    $serie = $data['serie'];

    $emisorRfc = $data['emisorRfc'];
    $emisorNombre = $data['emisorNombre'];
    $emisorRegimenFiscal = $data['emisorRegimenFiscal'];
    $receptorRfc = $data['receptorRfc'];
    $receptorNombre = $data['receptorNombre'];
    $usoCFDI = $data['usoCFDI'];

    /* Vamos a crear un XML con XMLWriter a partir de la matriz anterior. 
    Lo vamos a crear usando programación orientada a objetos. 
    Por lo tanto, empezamos creando un objeto de la clase XMLWriter.*/
    $objetoXML = new XMLWriter();

    // Estructura básica del XML
    $objetoXML->openURI("archivoXml.xml");
    $objetoXML->setIndent(true);
    $objetoXML->setIndentString("\t");
    $objetoXML->startDocument('1.0', 'utf-8');
    // Inicio del nodo raíz
    $objetoXML->startElement("cfdi:Comprobante");
    $objetoXML->writeAttribute("xmlns:cfdi", "http://www.sat.gob.mx/cfd/3");
    $objetoXML->writeAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
    $objetoXML->writeAttribute("xsi:schemaLocation", "http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd");
    $objetoXML->writeAttribute("Version", "3.3");
    $objetoXML->writeAttribute("LugarExpedicion", $lugarExpedicion);
    $objetoXML->writeAttribute("MetodoPago", $metodoPago);
    $objetoXML->writeAttribute("TipoDeComprobante", $tipoDeComprobante);
    $objetoXML->writeAttribute("Total", $total);
    $objetoXML->writeAttribute("Moneda", $moneda);
    if ($descuento != "") {
        $objetoXML->writeAttribute("Descuento", $descuento);
    }
    $objetoXML->writeAttribute("SubTotal", $subTotal);
    if ($condicionesDePago = "") {
        $objetoXML->writeAttribute("CondicionesDePago", $condicionesDePago);
    }
    $objetoXML->writeAttribute("FormaPago", $formaPago);
    if($folio != ""){
        $objetoXML->writeAttribute("Folio", $folio);
    }
    if($serie != ""){
        $objetoXML->writeAttribute("Serie", $serie);
    }
    $objetoXML->startElement("cfdi:Emisor");
    $objetoXML->writeAttribute("RegimenFiscal",$emisorRegimenFiscal);
    $objetoXML->writeAttribute("Nombre",$emisorNombre);
    $objetoXML->writeAttribute("Rfc",$emisorRfc);
    $objetoXML->endElement(); // Final del nodo raíz, "cfdi:Emisor"

    $objetoXML->startElement("cfdi:Receptor");
    $objetoXML->writeAttribute("Nombre",$receptorNombre);
    $objetoXML->writeAttribute("Rfc",$receptorRfc);
    $objetoXML->writeAttribute("UsoCFDI",$usoCFDI);
    $objetoXML->endElement(); // Final del nodo raíz, "cfdi:Receptor"

    $objetoXML->startElement("cfdi:Conceptos");

    $objetoXML->endElement(); // Final del nodo raíz, "cfdi:Conceptos"

    $objetoXML->endElement(); // Final del nodo raíz, "cfdi:Comprobante"
    $objetoXML->endDocument(); // Final del documento

    $objetoXML->endElement(); // Final del nodo raíz, "obras"
    $objetoXML->endDocument(); // Final del documento

	$result = array(
		'status' => 'succes',
		'code' => 200,
        'message' => 'xml creado correctamente',
        'Mensaje 2' => 'Eres un chongon we'
	);
	echo json_encode($result);

// use Slim\Slim;


// $app = new \Slim\App();

// $app->post('/createXml', function ($request, $response, $args){
//     $postdata = file_get_contents("php://input");
//     $data = json_decode($postdata,true);

//     $lugarExpedicion = $data['lugarExpedicion'];
//     $metodoPago = $data['metodoPago'];
//     $tipoDeComprobante = $data['tipoDeComprobante'];
//     $total = $data['total'];
//     $moneda = $data['moneda'];
//     $descuento = $data['descuento'];
//     $subTotal = $data['subtotal'];  
//     $condicionesDePago = $data['condicionesDePago'];
//     $formaPago = $data['formaPago'];
//     $folio = $data['folio'];
//     $serie = $data['serie'];

//     $emisorRfc = $data['emisorRfc'];
//     $emisorNombre = $data['emisorNombre'];
//     $emisorRegimenFiscal = $data['emisorRegimenFiscal'];
//     $receptorRfc = $data['receptorRfc'];
//     $receptorNombre = $data['receptorNombre'];
//     $usoCFDI = $data['usoCFDI'];

//     /* Vamos a crear un XML con XMLWriter a partir de la matriz anterior. 
//     Lo vamos a crear usando programación orientada a objetos. 
//     Por lo tanto, empezamos creando un objeto de la clase XMLWriter.*/
//     $objetoXML = new XMLWriter();

//     // Estructura básica del XML
//     $objetoXML->openURI("archivoXml.xml");
//     $objetoXML->setIndent(true);
//     $objetoXML->setIndentString("\t");
//     $objetoXML->startDocument('1.0', 'utf-8');
//     // Inicio del nodo raíz
//     $objetoXML->startElement("cfdi:Comprobante");
//     $objetoXML->writeAttribute("xmlns:cfdi", "http://www.sat.gob.mx/cfd/3");
//     $objetoXML->writeAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
//     $objetoXML->writeAttribute("xsi:schemaLocation", "http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd");
//     $objetoXML->writeAttribute("Version", "3.3");
//     $objetoXML->writeAttribute("LugarExpedicion", $lugarExpedicion);
//     $objetoXML->writeAttribute("MetodoPago", $metodoPago);
//     $objetoXML->writeAttribute("TipoDeComprobante", $tipoDeComprobante);
//     $objetoXML->writeAttribute("Total", $total);
//     $objetoXML->writeAttribute("Moneda", $moneda);
//     $objetoXML->writeAttribute("Descuento", $descuento);
//     $objetoXML->writeAttribute("SubTotal", $subTotal);
//     $objetoXML->writeAttribute("CondicionesDePago", $condicionesDePago);
//     $objetoXML->writeAttribute("FormaPago", $formaPago);
//     $objetoXML->writeAttribute("Folio", $folio);
//     $objetoXML->writeAttribute("Serie", $serie);

//     $objetoXML->startElement("cfdi:Emisor");
//     $objetoXML->writeAttribute("RegimenFiscal",$emisorRegimenFiscal);
//     $objetoXML->writeAttribute("Nombre",$emisorNombre);
//     $objetoXML->writeAttribute("Rfc",$emisorRfc);
//     $objetoXML->endElement(); // Final del nodo raíz, "cfdi:Emisor"

//     $objetoXML->startElement("cfdi:Receptor");
//     $objetoXML->writeAttribute("Nombre",$receptorNombre);
//     $objetoXML->writeAttribute("Rfc",$receptorRfc);
//     $objetoXML->writeAttribute("UsoCFDI",$usoCFDI);
//     $objetoXML->endElement(); // Final del nodo raíz, "cfdi:Receptor"

//     $objetoXML->startElement("cfdi:Conceptos");

//     $objetoXML->endElement(); // Final del nodo raíz, "cfdi:Conceptos"

//     $objetoXML->endElement(); // Final del nodo raíz, "cfdi:Comprobante"
//     $objetoXML->endDocument(); // Final del documento

//     $objetoXML->endElement(); // Final del nodo raíz, "obras"
//     $objetoXML->endDocument(); // Final del documento

// 	$result = array(
// 		'status' => 'succes',
// 		'code' => 200,
//         'message' => 'xml creado correctamente',
// 	);
// 	echo json_encode($result);
// });
// $app->run();

// foreach ($matrizDeObras as $obra){
//   $objetoXML->startElement("obra"); // Se inicia un elemento para cada obra.
//   // Atributo de la fecha de inicio del elemento obra
//   $objetoXML->writeAttribute("inicio", $obra["fecha_de_inicio"]);
//   // Atributo de la fecha de final del elemento obra
//   $objetoXML->writeAttribute("final", $obra["fecha_de_finalizacion"]);
//   // Atributo contratista del elemento obra
//   $objetoXML->writeAttribute("contratista", $obra["contratista"]);
//   // Atributo presupuesto del elemento obra.
//   $objetoXML->writeAttribute("presupuesto", $obra["presupuesto"]);
//   // Texto del nombre de la obra, dentro del elemento obra
//   $objetoXML->text("\n\t\t".$obra["obra"]."\n");
//   // Inicio del elemento anidado del personal técnico
//   $objetoXML->startElement("personal_tecnico");
//   // Atributo del número de miembros del personal técnico.
//   $objetoXML->writeAttribute("miembros", $obra["miembros_tecnicos"]);
//   // Para cada miembro del personal técnico se crea un elemento.
//   foreach ($obra["personal_tecnico"] as $keyMiembro=>$miembro){
//     $objetoXML->startElement("miembro");
//     // El cargo es un atributo del elmento del miembro técnico.
//     $objetoXML->writeAttribute("cargo", $keyMiembro);
//     // El nombre del miembro es el contenido del elemento del miembro técnico
//     $objetoXML->text($miembro);
//     $objetoXML->endElement();// Finaliza cada elelmento del miembro técnico.
//   }
//   $objetoXML->endElement(); // Final del elemento que cubre todos los miembros técnicos.
//   $objetoXML->fullEndElement (); // Final del elemento "obra" que cubre cada obra de la matriz.
// }

?>