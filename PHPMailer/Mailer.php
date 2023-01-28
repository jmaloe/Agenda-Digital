<?php
//incluimos la clase PHPMailer
require_once("class.phpmailer.php");
require_once("class.smtp.php");
require_once("thread.php");

class Correo
{

  var $asunto;  
  var $remitente, $nombreRemitente;
  var $contenito;
  var $direcciones;  

  function setAsunto($subject){
    $this->asunto = $subject;
  }

  function setRemitente($from, $name){
    $this->remitente = $from;
    $this->nombreRemitente = empty($name) ? $from : $name;
  }

  function setContenido($content){
    $this->contenido = $content;
  }

  function setDirecciones($emails){
    $this->direcciones = $emails;
  }

  function getAsunto(){
    return $this->asunto;
  }

  function getRemitente(){
    return $this->remitente;    
  }

  function getNombreRemitente(){    
    return $this->nombreRemitente;
  }

  function getContenido(){
    return $this->contenido;
  }

  function getDirecciones(){
    return $this->direcciones;
  }

  function enviarT()
  {
    try{
    echo "enviando....";
    $hilo = new Thread($this,'proceso');
    echo "objeto creado";
    $hilo->start();
    echo "enviado...";
    //return;
   }catch(Exception $e){
    echo $e->getMessage();
   }
  }

  static function prueba(){
    echo "prueba ejecutada";
  }

  function enviar()
  {    
    //instancio un objeto de la clase PHPMailer
    $mail = new PHPMailer(); // defaults to using php "mail()"

    $mail->isSMTP(); // telling the class to use SMTP

    //$mail->SMTPDebug  = true;                    // enables SMTP debug information (for testing) 1 = errors and messages, 2 = messages only
    //$mail->Debugoutput = 'html';

    $mail->SMTPAuth   = true; // enable SMTP authentication
    
    $mail->SMTPSecure = "TLS"; // 465 SSL, 587 TLS sets the prefix to the servier    
    $mail->Host       = "tls://montebello.unach.mx:587"; // sets GMAIL as the SMTP server
    $mail->Port       = 587; // set the SMTP port for the GMAIL server

    $mail->Username   = "desarrollo.tecnologico@unach.mx"; // MAIL username
    $mail->Password   = "desarrollo01"; // MAIL password

    $mail->IsHTML(true);
    $mail->CharSet = 'UTF-8';
    //defino el email y nombre del remitente del mensaje
    /*
    if(empty($this->getRemitente())){
      echo "No se especificó remitente";
      return;
    }*/

    $mail->setFrom($this->getRemitente(), $this->getNombreRemitente());

    //defino la dirección de email de "reply", a la que responder los mensajes
    //Obs: es bueno dejar la misma dirección que el From, para no caer en spam
    $mail->addReplyTo($this->getRemitente(), $this->getNombreRemitente());

    //Añado un asunto al mensaje
    $mail->Subject = $this->getAsunto();

    //Puedo definir un cuerpo alternativo del mensaje, que contenga solo texto
    //$mail­>AltBody = "Cuerpo alternativo del mensaje";

    //inserto el texto del mensaje en formato HTML
    $mail->msgHTML($this->getContenido());

    //asigno un archivo adjunto al mensaje
    //$mail­->AddAttachment("ruta/archivo_adjunto.gif");

    /*Envío masivo de correos*/
    //This iterator syntax only works in PHP 5.4+
    foreach ($this->direcciones as $row)
    {
     /*comprobamos que el email sea valido*/ 
     if(!empty($row['email']))
     {
//echo $row['email']." ".$row['nombre_completo'];
      $mail->addAddress($row['email'], $row['nombre_completo']);     

      if (!$mail->send())
      {
        //echo "<br>Error de envío a (" . str_replace("@", "&#64;", $row["email"]) . ') ' . $mail->ErrorInfo . '<br>';        
      }
      else
      {
        //echo "Mensaje enviado: " . $row['nombre_completo'] . ' (' . str_replace("@", "&#64;", $row['email']) . ')<br />';
      }
      // Clear all addresses and attachments for next loop
      $mail->clearAddresses();
      //$mail->clearAttachments();
     }
    }
  }

}

/*
   $correo = new Correo(1);
   $correo->setAsunto("EVENTO: Concatenar asunto");
    //echo "EVENTO: ".$vars['subject'];
   $correo->setRemitente("jesus.malo@unach.mx","Jesús Malo Escobar");
    //echo "FROM:".$phpc_user->get_email()." - ".$phpc_user->get_nombre_completo();
   
   $contenido = "Este será el contenido con toda la información del evento.";
    //echo "CONTENIDO:".$contenido->toString();
   $correo->setContenido($contenido); //false para no retornar los controles o botones: display_event.php

   $direcciones = Array( 
    array('email' => 'dic.malo@gmail.com', 'nombre_completo' => 'Jesús Malo')/*,
    array('email' => 'dic.malo@gmail.com', 'nombre_completo' => 'Jesús Malo Escobar'),
    array('email' => 'dic_malo@hotmail.com', 'nombre_completo' => 'Jesús Malo Escobar')
    ); 
   
    //print_r($direcciones);
   $correo->setDirecciones($direcciones);
   
   $correo->enviar();
*/

?>