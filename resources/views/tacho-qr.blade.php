<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pantalla del Tacho Inteligente</title>
    <!-- Incluir librería para generar QR -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #111827;
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
        }
        h1 {
            color: #10B981;
            margin-bottom: 5px;
        }
        p {
            color: #9CA3AF;
            margin-bottom: 30px;
        }
        #qrcode {
            background: white;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }
        .status {
            margin-top: 20px;
            font-size: 14px;
            color: #6B7280;
        }
    </style>
</head>
<body>

    <h1>Ecoscan Tacho #1</h1>
    <p>Escanea este código con tu app móvil para empezar a reciclar</p>

    <!-- Contenedor del QR -->
    <div id="qrcode"></div>

    <div class="status" id="status-text">Generando código...</div>

    <script>
        let currentPin = '';
        const qrcodeContainer = document.getElementById("qrcode");
        const statusText = document.getElementById("status-text");

        // Instancia del QR
        const qrcode = new QRCode(qrcodeContainer, {
            text: "Cargando...",
            width: 250,
            height: 250,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });

        async function fetchPin() {
            try {
                // Hacemos la petición a la API de Laravel
                const response = await fetch('/api/v1/tacho/1/pin');
                if (!response.ok) throw new Error('Error en la red');
                
                const data = await response.json();
                
                // Si el PIN cambió, actualizamos el QR
                if (data.status === 'success' && data.pin_actual !== currentPin) {
                    currentPin = data.pin_actual;
                    qrcode.clear(); // Limpia el QR viejo
                    qrcode.makeCode(currentPin); // Dibuja el nuevo QR
                    statusText.innerText = "Código de seguridad actualizado automáticamente";
                    
                    // Efecto visual de actualización
                    qrcodeContainer.style.transform = "scale(1.05)";
                    setTimeout(() => qrcodeContainer.style.transform = "scale(1)", 200);
                }
            } catch (error) {
                console.error("Error obteniendo el PIN:", error);
                statusText.innerText = "Error de conexión con el servidor";
            }
        }

        // Obtener el PIN inmediatamente al cargar
        fetchPin();

        // Seguir preguntando cada 3 segundos
        setInterval(fetchPin, 3000);
    </script>
</body>
</html>
