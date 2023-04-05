<?php

namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;

class Email {

    public $nombre;
    public $email;
    public $token;

    public function __construct($nombre, $email, $token) 
    {
        $this->nombre = $nombre;
        $this->email = $email;
        $this->token = $token;
    }

    public function enviarConfirmacion() {
        //crear objeto de email
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Port = 2525;
        $mail->Username = '33b297a0fc8515';
        $mail->Password = 'd6696a265bc78c';
        

        $mail->setFrom('cuentas@appsalon.com');
        $mail->addAddress('cuentas@appsalon.com', 'AppSalon.com');
        $mail->Subject = 'Confirma tu cuenta';

        //Set html
        $mail->isHTML(TRUE);
        $mail->CharSet = 'UTF-8';

        $contenido = "<html>";
        $contenido .= "<p><strong>Hola " . $this->nombre . "</strong> Has creado tu cuenta en App Salon, confirma tu cuenta con el enlace</p>";
        $contenido .= "<p>Presiona aqui: <a href='http://localhost:3000/confirmar-cuenta?token=" . $this->token . "'>Confirmar Cuenta</a></p>";
        $contenido .= "<p>Si no la solicitaste, ignoralo</p>";
        $contenido .= "</html>";
        $mail->Body = $contenido;

        //Enviar Email
        $mail->send();
    }

    public function enviarInstrucciones() {

             //crear objeto de email
             $mail = new PHPMailer();
             $mail->isSMTP();
             $mail->Host = 'sandbox.smtp.mailtrap.io';
             $mail->SMTPAuth = true;
             $mail->Port = 2525;
             $mail->Username = '33b297a0fc8515';
             $mail->Password = 'd6696a265bc78c';
             
     
             $mail->setFrom('cuentas@appsalon.com');
             $mail->addAddress('cuentas@appsalon.com', 'AppSalon.com');
             $mail->Subject = 'Restablece tu password';
     
             //Set html
             $mail->isHTML(TRUE);
             $mail->CharSet = 'UTF-8';
     
             $contenido = "<html>";
             $contenido .= "<p><strong>Hola " . $this->nombre . "</strong> Has solicitado reestablecer tu password, usa el sigueinte enlace</p>";
             $contenido .= "<p>Presiona aqui: <a href='http://localhost:3000/recuperar?token=" . $this->token . "'>Restablecer Password</a></p>";
             $contenido .= "<p>Si no la solicitaste, ignoralo</p>";
             $contenido .= "</html>";
             $mail->Body = $contenido;
     
             //Enviar Email
             $mail->send();

    }
}