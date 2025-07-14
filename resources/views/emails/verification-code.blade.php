<!-- resources/views/emails/verification-code.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Código de Verificación - SADIoT</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f5f7fa; margin:0; padding:20px;">
    <div style="max-width: 600px; margin: auto; background-color: #ffffff; 
                border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 30px;">


        <h2 style="color: #04874a; text-align: center; margin-bottom: 20px;">👋 ¡Hola!</h2>

        <p style="font-size: 16px; color: #333;">
            Gracias por registrarte en <strong>SADIoT</strong>, la plataforma integral para docentes, investigadores y estudiantes interesados en el Internet de las Cosas.
        </p>

        <p style="font-size: 16px; color: #333;">
            Para continuar con tu registro, por favor ingresa el siguiente código de verificación:
        </p>

        <div style="text-align: center; margin: 30px 0;">
            <span style="font-size: 48px; font-weight: bold; color: #04874a; letter-spacing: 8px;">
                {{ $code }}
            </span>
        </div>

        <p style="font-size: 14px; color: #666;">
            Este código es válido por un tiempo limitado. Si no solicitaste este código, por favor ignora este mensaje.
        </p>

        <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">

        <p style="font-size: 14px; color: #999; text-align: center;">
            SADIoT<br>
            "Donde hay un sensor, hay una variable que puede ser medida."
        </p>
    </div>
</body>
</html>
