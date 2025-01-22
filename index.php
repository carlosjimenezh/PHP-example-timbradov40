<?php 
    require_once "cliente_formasdigitales.php";
    header('Content-type: text/html;charset=utf-8');
    try{
      set_time_limit(0);
      date_default_timezone_set("America/Mexico_City");

      $filexml = dirname(__FILE__) . "/resources/cfdi_v40_generico.xml";
      $filecer = dirname(__FILE__) . "/resources/CSD_Escuela_Kemper_Urgate_EKU9003173C9_20190617_131753s_certificado.pem";
      $filekey = dirname(__FILE__) . "/resources/CSD_Escuela_Kemper_Urgate_EKU9003173C9_20190617_131753_llavePrivada.pem";

      $clienteFD = new ClienteFormasDigitales($filexml);
      $clienteFD->getDate();
      

      $autentica = new Autenticar();
		  $autentica->usuario = "pruebasWS";
		  $autentica->password = "pruebasWS"; //se crea el objeto autenticar con el usuario y contraseña del servicio de formas digitales 
      
      $parametros = new Parametros();
      $parametros->accesos = $autentica;
      $parametros->comprobante=$clienteFD->loadCertKey($filecer,$filekey);

      $responseTimbre = $clienteFD->timbrarXML($parametros);//se llama al método timbrarXML y se mandan los parametros (acessos y comprobante)


      $datosCertificado = openssl_x509_parse(file_get_contents($filecer));
      $fechaInicio = date('Y-m-d H:i:s', $datosCertificado['validFrom_time_t']); //Se obtiene la fecha de inicio de vigencia del certificado
      $fechaFin = date('Y-m-d H:i:s', $datosCertificado['validTo_time_t']); //Se obtiene la fecha de fin de vigencia del certificado
      echo "Fecha Inicio: $fechaInicio <br>";
      echo "Fecha Fin: $fechaFin <br>";


      if(isset($responseTimbre->acuseCFDI->error)){ //Si ocurrio un error al momento de timbrar el xml enviado se mostrará en pantalla
        echo "codigoErr: " . $responseTimbre->acuseCFDI->error. "<br>";
      }
  
      if($responseTimbre->acuseCFDI->xmlTimbrado){//Si el xml fue timbrado con exito se mostrará en pantalla
        echo 'XML TMIBRADO:<BR> <textarea>' . $responseTimbre->acuseCFDI->xmlTimbrado . '</textarea>';
      }


    }catch(Exception $ex){
      print("Error: $ex");
    }

    class Autenticar{
      public $usuario;
      public $password;
    }
    
    
    class Parametros{
      public $accesos;
      public $comprobante;
    }
?>