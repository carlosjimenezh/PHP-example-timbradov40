<?php
    class ClienteFormasDigitales{
        
        public $xml;
        public $cadenaoriginalxslt;

        public function __construct($xmlpath){
            //Constructor de la clase
            $this->xml = new DOMDocument();
            $this->xml = simplexml_load_file($xmlpath) or die("XML invalido");
            $this->cadenaoriginalxslt = dirname(__FILE__)."/resources/cadenaoriginal_4_0.xslt";
        }

        public function loadCertKey($filecer,$fileKey){        
            //carga certificado
            $data_cert = openssl_x509_parse(file_get_contents($filecer));
            $serial = $this->getNoCertificado($data_cert['serialNumberHex']); //Obtiene numero certificado y lo carga al xml  
            $this->xml['NoCertificado'] = $serial;
            
            $cert = file_get_contents(dirname(__FILE__) . "/resources/ESCUELA_KEMPER_URGATE_EKU9003173C9.cer");
            $certificado = str_replace(array('\n', '\r'), '', base64_encode($cert));
            $this->xml['Certificado'] = $certificado;//Carga el certificado en base64
            $this->xml->saveXML();
            $cadenaorignal =$this->generaCadenaOriginal();//Obtiene la cadena original
            $private = openssl_pkey_get_private(file_get_contents($fileKey));
            openssl_sign($cadenaorignal,$signature,$private,"sha256WithRSAEncryption");
            $sello = base64_encode($signature);
            $this->xml['Sello'] = $sello;//Carga el sello en el xml 

            return $this->xml->saveXML();//Regresa el xml con los datos actualizados

        }

        public function timbrarXML($parametros){
            $client = new SoapClient('http://dev33.facturacfdi.mx/WSTimbradoCFDIService?wsdl');//Crea cliente soap del webservice de formas digitales
		    return $client->TimbrarCFDI($parametros);//Realiza el llamado al método de timbrado.
        }

        public function generaCadenaOriginal(){
            $XSL = new DOMDocument();
            $XSL->load($this->cadenaoriginalxslt);
            $proc = new XSLTProcessor();
            @$proc->importStylesheet($XSL);
            $cadena = $proc->transformToXml($this->xml);
            return $cadena;
        }

        public function getDate(){
            $date = date('Y-m-d H:i:s');
            $this->xml['Fecha']=str_replace(' ','T',$date);
            // $this->xml['Fecha']="2023-03-01T16:17:18"; //Fecha de prueba
        }
        
        function getNoCertificado($serial){
			$noCertificado = "";
			
			if((strlen($serial) % 2) == 1){
				$serial = " " . $serial;
			}

			for($i=0; $i < strlen($serial)/2; $i++){
				$aux = substr($serial, $i*2, ($i * 2) + 2);
				$noCertificado .=  substr($aux,1,1);
			}

			return $noCertificado;
		}

    }
?>