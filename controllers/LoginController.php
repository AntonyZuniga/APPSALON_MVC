<?php

namespace Controllers;

use MVC\Router;
use Classes\Email;
use Model\Usuario;

class LoginController {
    public static function login(Router $router) {
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new Usuario($_POST);

            $alertas = $auth->validarLogin();
         
            if(empty($alertas)){
                //comprobar que exita
                $usuario = Usuario::where('email', $auth->email);
                
                if($usuario) {
                    //verifica el password
                    if($usuario->comprobarPasswordAndVerificado($auth->password)) {
                       

                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre ." ". $usuario->apellido;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        //Redireccionar
                        if($usuario->admin === "1"){
                            $_SESSION['admin'] = $usuario->admin ?? null;

                            header('Location: /admin');
                        }else{
                            header('Location: /cita');
                        }

                    }
                }else{
                    Usuario::setAlerta('error', 'Usuario no encontrado');
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/login', [
            'alertas' => $alertas
        ]);
    }

    public static function logout() {

      

        $_SESSION = [];

        header('Location: /');
    }

    public static function olvide(Router $router) {

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new Usuario($_POST);
            $alertas = $auth->validarEmail();

            if(empty($alertas)) {
                $usuario = Usuario::where('email', $auth->email); //l columna, segundo el dato, es auth porque crea el objeto con los datos 
                
                if($usuario && $usuario->confirmado === "1") {

                    //generar otro token
                    $usuario->crearToken();
                    $usuario->guardar();

                    //Enviar el email
                    $email = new Email($usuario->nombre, $usuario->email, $usuario->token);
                    $email->enviarInstrucciones();

                    //Alerta de exito
                    Usuario::setAlerta('exito', 'Revisa tu email');

                }else{
                    Usuario::setAlerta('error', 'El usuario no existe o no esta confirmado');
                    
                }   
            }
        }
        $alertas = Usuario::getAlertas();

        $router->render('auth/olvide-password', [
            'alertas' => $alertas
        ]);
    }

    public static function recuperar(Router $router) {

        $alertas = [];
        $error = false;
        

        $token = s($_GET['token']);

        //Buscar usuario por token
        $usuario = Usuario::where('token', $token);



        if(empty($usuario)) {
            Usuario::setAlerta('error', 'Token no valido');
           $error = true;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            //lEER NUEVO PASSWROD

            $password = new Usuario($_POST); //con esto creo el objeto donde guardo la contraseña, me voy a model usuario
            $alertas = $password->validarPassword();

            if(empty($alertas)){
                $usuario->password = null; //quito la contr guardada

               
                $usuario->password = $password->password; //aqui entro al objeto del usuario y le asigno la nueva contra
                $usuario->hashPassword();
                $usuario->token = null;

                $resultado = $usuario->guardar();
                if($resultado) {
                    header('Location: /');
                }
            }
           
        }

        // debuguear($usuario);

        $alertas = Usuario::getAlertas();
        $router->render('auth/recuperar-password', [
            'alertas' => $alertas,
            'error' => $error
        ]);
    }

    public static function crear(Router $router) {

        $usuario = new Usuario;

        //Alertas vacias
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();

            //Revisar que alertas este vacio
            if(empty($alertas)) {
                //verificar que el usuario no este registrado
                $resultado = $usuario->existeUsuario();

                if($resultado->num_rows){
                    $alertas =  Usuario::getAlertas();
                } else {
                    //Hashear password
                    $usuario->hashPassword();

                    //generar un token unico
                    $usuario->crearToken();

                    //Enviar email
                    $email = new Email($usuario->nombre, $usuario->email, $usuario->token);

                    $email->enviarConfirmacion();

                   
                    //crear el usuario
                    $resultado = $usuario->guardar();
                    if($resultado) {
                       header('Location: /mensaje');
                    }


                   
                }
            }
        }

        $router->render('auth/crear-cuenta', [
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function mensaje(Router $router) {
        $router->render('auth/mensaje');
    }

    public static function confirmar(Router $router) {
        $alertas = [];
        $token = s($_GET['token']);

        $usuario = Usuario::where('token', $token);

        if(empty($usuario)) {
            // mostratr mensaje de error
            Usuario::setAlerta('error', 'Token no valido');
        }else{
            // modificar a usuario confirmado
            $usuario->confirmado = "1";
            $usuario->token = null;
            $usuario->guardar();
            Usuario::setAlerta('exito', 'Cuenta verificada');
        }

        //obtener alertas
        $alertas = Usuario::getAlertas();
        //renderizar vista
        $router->render('auth/confirmar-cuenta', [
            'alertas' => $alertas
        ]);
    }
}