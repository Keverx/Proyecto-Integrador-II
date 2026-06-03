<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifica tu cuenta - EcoScan</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            color: #333333;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #2e7d32; /* Verde ecológico */
            color: #ffffff;
            text-align: center;
            padding: 30px 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            letter-spacing: 1px;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        .content p {
            font-size: 16px;
            line-height: 1.6;
            color: #555555;
            margin-bottom: 20px;
        }
        .code-box {
            background-color: #e8f5e9;
            border: 2px dashed #4caf50;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            display: inline-block;
        }
        .code {
            font-size: 36px;
            font-weight: bold;
            color: #2e7d32;
            letter-spacing: 5px;
            margin: 0;
        }
        .footer {
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #999999;
            border-top: 1px solid #eeeeee;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>EcoScan</h1>
    </div>
    
    <div class="content">
        <h2>¡Hola, {{ $nombre }}!</h2>
        <p>Gracias por unirte a EcoScan. Para completar tu registro y comenzar a reciclar, por favor ingresa el siguiente código de verificación en la aplicación:</p>
        
        <div class="code-box">
            <p class="code">{{ $codigo }}</p>
        </div>
        
        <p>Si tú no solicitaste este código, puedes ignorar este correo de forma segura.</p>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} EcoScan. Todos los derechos reservados.</p>
        <p>Por un mundo más verde y sostenible 🌍</p>
    </div>
</div>

</body>
</html>
