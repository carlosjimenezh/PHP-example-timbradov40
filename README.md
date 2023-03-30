# Ejemplo de timbrado CFDI 4.0 en php

La clase **ClienteFormasDigitales** es la que nos permitirá a timbrar un CFDI 4.0 

La función **loadCertKey** permite obtener el número de certificado y el certificado en base64 además de realizar el llamado a las funciones
**getNoCertificado** y **generaCadenaOriginal**
```PHP

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

```

Con la funcíon **generaCadenaOriginal** se va a poder generar la cadena original cargando el archivo xslt.
```PHP

	public function generaCadenaOriginal(){
            $XSL = new DOMDocument();
            $XSL->load($this->cadenaoriginalxslt);
            $proc = new XSLTProcessor();
            @$proc->importStylesheet($XSL);
            $cadena = $proc->transformToXml($this->xml);
            return $cadena;
    }

```

Tambien se cuenta con la función **getDate** para poder obtener la fecha actual del equipo y asignarla al xml con el formato que solicita el SAT.
```PHP

	public function getDate(){
            $date = date('Y-m-d H:i:s');
            $this->xml['Fecha']=str_replace(' ','T',$date);
     }

```
 
 **getNoCertificado** permite extraer el número del certificado.
```PHP
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
```