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

// require 'vendor/autoload.php';

// Funcion para generar Sello y Cadena Original
function actualizarSello($archivoXml){
    //Leer XML
        $xmlDoc = new DOMDocument();
        $xmlDoc->load($archivoXml);
        
        //Cambiar Fecha a actual y guardar en archivo
        date_default_timezone_set('America/Mexico_City');
        $date = date('Y-m-d_H:i:s');
        $date = str_replace("_", "T", $date);
        $xmlDoc->firstChild->setAttribute('Fecha', $date);
        $xmlString = $xmlDoc->saveXML();
        file_put_contents($archivoXml, $xmlString);

        //Crear cadena original
        $xslt = new DOMDocument();
        $xslt->load('timbox-php/cadenaoriginal_3_3.xslt');
        $xml = new DOMDocument;
        $xml->load($archivoXml);

        $proc = new XSLTProcessor;
        @$proc->importStyleSheet($xslt); // attach the xsl rules
        $cadena = $proc->transformToXML($xml);
        file_put_contents('timbox-php/llaves/AAA010101AAA/cadena_original.txt', $cadena);
        
        //Firmar cadena y obtener el digest
        $key = file_get_contents('timbox-php/llaves/AAA010101AAA/CSD10_AAA010101AAA.key.pem');
        openssl_sign($cadena, $digest, $key, OPENSSL_ALGO_SHA256);
        file_put_contents('timbox-php/llaves/AAA010101AAA/digest.txt', $digest);
        
        //Generar Sello
        $sello = base64_encode($digest);
        file_put_contents('timbox-php/llaves/AAA010101AAA/sello.txt', $sello);

        //Actualizar el sello del XML
        $xmlDoc->firstChild->setAttribute('Sello', $sello);
        $xmlString = $xmlDoc->saveXML();
        file_put_contents($archivoXml, $xmlString);    
    }


$postdata = file_get_contents("php://input");
    $data = json_decode($postdata,true);

    $lugarExpedicion = $data['lugarExpedicion'];
    $metodoPago = $data['metodoPago'];
    $tipoDeComprobante = $data['tipoDeComprobante'];
    $tipoDeCambio = $data['tipoDeCambio'];
    $moneda = $data['moneda'];
    $descuento = $data['descuento']; 
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
    $total = 0;
    $subTotal = 0;

    // Variable de conceptos
    $conceptos = $data['conceptos'];

    $totalRetenidos = 0;
    $totalTraslados = 0;

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
    $objetoXML->writeAttribute("Moneda", $moneda);

    if ($descuento != "") {
        $objetoXML->writeAttribute("Descuento", $descuento);
    }
    if ($condicionesDePago != "") {
        $objetoXML->writeAttribute("CondicionesDePago", $condicionesDePago);
    }
    $objetoXML->writeAttribute("FormaPago", $formaPago);
    if($folio != ""){
        $objetoXML->writeAttribute("Folio", $folio);
    }
    if($serie != ""){
        $objetoXML->writeAttribute("Serie", $serie);
    }
    if($tipoDeCambio != ""){
        $objetoXML->writeAttribute("TipoDeCambio", $tipoDeCambio);
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
    $cont = count($conceptos);
    $objetoXML->startElement("cfdi:Conceptos");
    for ($i=0; $i < $cont ; $i++) { 
        $objetoXML->startElement("cfdi:Concepto");
        $objetoXML->writeAttribute("Importe",$conceptos[$i]['importe']);
        $subTotal = $subTotal + $conceptos[$i]['importe'];
        $objetoXML->writeAttribute("ValorUnitario",$conceptos[$i]['valorUnitario']);
        $objetoXML->writeAttribute("Descripcion",$conceptos[$i]['descripcion']);
        $objetoXML->writeAttribute("Unidad",$conceptos[$i]['unidad']);
        $objetoXML->writeAttribute("ClaveUnidad",$conceptos[$i]['claveUnidad']);
        $objetoXML->writeAttribute("Cantidad",$conceptos[$i]['cantidad']);
        $objetoXML->writeAttribute("ClaveProdServ",$conceptos[$i]['claveProdServ']);

            // INICIO IMPUESTOS/CONCEPTOS
            $objetoXML->startElement("cfdi:Impuestos");
            // VARIABLES DE BANDERAS PARA DETERMINAR SI TIENE RETENIDOS Y TRANSLADOS
            $conRetenidos = false;
            $conTransladados = false;
            // FOR - RECORRE CADA IMPUESTO Y DETERMINA SI HAY RETENIDOS O TRASLADO
            for ($j=0; $j < count($conceptos[$i]['impuestos']) ; $j++) {
                if ($conceptos[$i]['impuestos'][$j]['claseImpuesto'] === "retenido") {
                    $conRetenidos = true;
                }else{
                    $conTransladados = true;
                }
            }
            // SI HAY TRASLADOS SE EJECUTA LO SIGUIENTE
            if($conTransladados){
                
                $objetoXML->startElement("cfdi:Traslados"); //OPEN ETIQUETA TRASLADOS
                for ($j=0; $j < count($conceptos[$i]['impuestos']) ; $j++) {
                    if($conceptos[$i]['impuestos'][$j]['claseImpuesto'] === 'transladado'){
                        $totalTraslados = $totalTraslados + $conceptos[$i]['impuestos'][$j]['importe'];
                        $objetoXML->startElement("cfdi:Traslado"); //SE ABRE ETIQUETA TRASLADO
                        $objetoXML->writeAttribute("Importe",$conceptos[$i]['impuestos'][$j]['importe']);
                        $objetoXML->writeAttribute("TasaOCuota","0".$conceptos[$i]['impuestos'][$j]['valor'] ."0000");
                        $objetoXML->writeAttribute("TipoFactor",$conceptos[$i]['impuestos'][$j]['tipo']);
                        $objetoXML->writeAttribute("Impuesto",$conceptos[$i]['impuestos'][$j]['idImpuesto']);
                        $objetoXML->writeAttribute("Base",$conceptos[$i]['impuestos'][$j]['base']);
                        $objetoXML->endElement();   //SE CIERRA ETIQUETA TRASLADO
                    }
                }
                $objetoXML->endElement(); //SE CIERRA TRASLADOS
            }
            if($conRetenidos){
                // SI SE DETECTARON RETENIDOS SE EJECUTA LO SIGUIENTE
                
                $objetoXML->startElement("cfdi:Retenciones");
                for ($j=0; $j < count($conceptos[$i]['impuestos']) ; $j++) {
                    if ($conceptos[$i]['impuestos'][$j]['claseImpuesto'] === "retenido") {

                        $totalRetenidos = $totalRetenidos + $conceptos[$i]['impuestos'][$j]['importe'];
                        $objetoXML->startElement("cfdi:Retencion"); //SE ABRE RETENIDO
                        $objetoXML->writeAttribute("Importe",$conceptos[$i]['impuestos'][$j]['importe']);
                        $objetoXML->writeAttribute("TasaOCuota","0".$conceptos[$i]['impuestos'][$j]['valor'] ."0000");
                        $objetoXML->writeAttribute("TipoFactor",$conceptos[$i]['impuestos'][$j]['tipo']);
                        $objetoXML->writeAttribute("Impuesto",$conceptos[$i]['impuestos'][$j]['idImpuesto']);
                        $objetoXML->writeAttribute("Base",$conceptos[$i]['impuestos'][$j]['base']);
                        $objetoXML->endElement(); //SE CIERRA RETENIDO
                    }
                }
                $objetoXML->endElement(); //SE CIERRA RETENCIONES
            }

            $objetoXML->endElement(); // Final del nodo raíz, "cfdi:Imnpuestos"

        $objetoXML->endElement(); // Final del nodo raíz, "cfdi:Concepto"
    }
    $objetoXML->endElement(); // Final del nodo raíz, "cfdi:Conceptos"

    //CODIGO DE AGRUPACION DE IMPUESTOS
        $objetoXML->startElement("cfdi:Impuestos"); //SE ABRE IMPUS
        if( $conTransladados){
            $objetoXML->writeAttribute("TotalImpuestosTrasladados",$totalTraslados);

        }
        if( $conRetenidos ){
            $objetoXML->writeAttribute("TotalImpuestosRetenidos",$totalRetenidos);
        }
        for ($i=0; $i < $cont ; $i++) {
            if( $conRetenidos ){
                // FOR PARA CAPTURAR RETENIDOS
                $objetoXML->startElement("cfdi:Retenciones");
                for ($j=0; $j < count($conceptos[$i]['impuestos']) ; $j++) {
                    if ($conceptos[$i]['impuestos'][$j]['claseImpuesto'] === "retenido") {
    
                        $objetoXML->startElement("cfdi:Retencion"); //SE ABRE RETENIDO
                        $objetoXML->writeAttribute("Importe",$conceptos[$i]['impuestos'][$j]['importe']);
                        $objetoXML->writeAttribute("TasaOCuota","0".$conceptos[$i]['impuestos'][$j]['valor'] ."0000");
                        $objetoXML->writeAttribute("TipoFactor",$conceptos[$i]['impuestos'][$j]['tipo']);
                        $objetoXML->writeAttribute("Impuesto",$conceptos[$i]['impuestos'][$j]['idImpuesto']);
                        $objetoXML->endElement(); //SE CIERRA RETENIDO
                    }
                }
                $objetoXML->endElement();
            }
            if( $conTransladados ){
                $objetoXML->startElement("cfdi:Traslados"); //OPEN ETIQUETA TRASLADOS
                    for ($j=0; $j < count($conceptos[$i]['impuestos']) ; $j++) {
                        if($conceptos[$i]['impuestos'][$j]['claseImpuesto'] === 'transladado'){
                            $objetoXML->startElement("cfdi:Traslado"); //SE ABRE ETIQUETA TRASLADO
                            $objetoXML->writeAttribute("Importe",$conceptos[$i]['impuestos'][$j]['importe']);
                            $objetoXML->writeAttribute("TasaOCuota","0".$conceptos[$i]['impuestos'][$j]['valor'] ."0000");
                            $objetoXML->writeAttribute("TipoFactor",$conceptos[$i]['impuestos'][$j]['tipo']);
                            $objetoXML->writeAttribute("Impuesto",$conceptos[$i]['impuestos'][$j]['idImpuesto']);
                            $objetoXML->endElement();   //SE CIERRA ETIQUETA TRASLADO
                        }
                    }
                    $objetoXML->endElement(); //SE CIERRA TRASLADOS
            }
        }

        $total = $subTotal - $totalRetenidos + $totalTraslados;

    $objetoXML->endElement(); // Final del nodo raíz, "cfdi:Comprobante"
    $objetoXML->endDocument();  

    

    $file = "timbox-php/llaves/AAA010101AAA/certificado.txt";
    $fp = fopen($file, "r");
    $certificado = fread($fp, filesize($file));
    fclose($fp);
    $fileNo = "timbox-php/llaves/AAA010101AAA/noCertificado.txt";
    $fp = fopen($fileNo, "r");
    $noCertificado = fread($fp, filesize($file));
    fclose($fp);
    
    $archivoXml = "archivoXml.xml";
    $xmlDoc = new DOMDocument();
    $xmlDoc->load($archivoXml);
    //Cambiar Fecha a actual y guardar en archivo
    $xmlDoc->firstChild->setAttribute('SubTotal', $subTotal);
    $xmlDoc->firstChild->setAttribute('Total', $total);
    $xmlDoc->firstChild->setAttribute('Certificado', $certificado);
    $xmlDoc->firstChild->setAttribute('Certificado', $certificado);
    $xmlDoc->firstChild->setAttribute('NoCertificado', $noCertificado);
    $xmlString = $xmlDoc->saveXML();
    file_put_contents($archivoXml, $xmlString);
    
    $result = array(
		'status' => 'succes',
		'code' => 200,
        'message' => 'xml creado correctamente',
        'Mensaje 2' => 'Eres un chongon we'
    );





    //parametros para conexion al Webservice (URL de Pruebas)
    $wsdl_url = "https://staging.ws.timbox.com.mx/timbrado_cfdi33/wsdl";
    $wsdl_usuario = "BVP980326TT1";
    $wsdl_contrasena = "NsTMyGs9JtmCam7_bpyj";
    $ruta_xml = "archivoXml.xml";


    actualizarSello($ruta_xml);
    // convertir la cadena del xml en base64
    $documento_xml = file_get_contents($ruta_xml);
    $xml_base64 = base64_encode($documento_xml);

    //crear un cliente para hacer la petición al WS
    $cliente = new SoapClient($wsdl_url, array(
        'trace' => 1,
        'use' => SOAP_LITERAL,
    ));

    //parametros para llamar la funcion timbrar_cfdi
    $parametros = array(
        "username" => $wsdl_usuario,
        "password" => $wsdl_contrasena,
        "sxml" => $xml_base64,
    );

    try {
        //llamar la funcion timbrar_cfdi
        $respuesta = $cliente->__soapCall("timbrar_cfdi", $parametros);
        //imprimir el contenido del XML timbrado
        echo htmlspecialchars($respuesta->xml);

    } catch (Exception $exception) {
        //imprimir los mensajes de la excepcion
        echo "# del error: " . $exception->getCode() . "\n";
        echo "Descripción del error: " . $exception->getMessage() . "\n";
    }

	echo json_encode($result);
?>