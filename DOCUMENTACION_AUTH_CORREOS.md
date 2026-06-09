#  Documentación de Cambios Backend: Autenticación y Verificación por Correo

Este documento detalla la lógica implementada en el backend de EcoScan para el flujo de registro, verificación de correo electrónico (OTP) y recuperación de contraseñas. Servirá como guía para entender cómo se protegen las cuentas y qué información viaja hacia la base de datos y hacia la aplicación móvil/Dashboard.

---

## 1. Cambios en la Base de Datos (Tabla `usuarios`) 

Para soportar los códigos de verificación, la tabla `usuarios` (en la migración `2026_04_30_000000_create_ecoscan_v2_tables.php`) cuenta con las siguientes columnas clave:
- **`estado_cuenta`**: `ENUM('PENDIENTE', 'VERIFICADO', 'BLOQUEADO')`. Por defecto es `'PENDIENTE'`. Un usuario no puede hacer login si no está verificado.
- **`codigo_verificacion`**: Guarda un string numérico de 6 dígitos (ej. `'482910'`) enviado al momento de registrarse. Se vuelve nulo cuando la cuenta se verifica.
- **`token_recuperacion`**: Guarda el código temporal enviado cuando un usuario olvida su contraseña.
- **`expiracion_token`**: Guarda la fecha y hora límite de validez del código de recuperación (usualmente 15 minutos).

---

## 2. Generación de Códigos y Correos (`AuthService.php`) 

Toda la lógica de negocio se encuentra centralizada en `app/Services/AuthService.php`.

### A. Registro y Generación del Código OTP
Cuando un usuario se registra (`registerUser`):
1. El sistema genera un código de 6 dígitos aleatorio usando `sprintf("%06d", mt_rand(1, 999999))`. Esto garantiza que siempre tenga ceros a la izquierda si el número es pequeño.
2. Se crea el usuario en estado `'PENDIENTE'` con la contraseña encriptada (Hash).
3. Se invoca a la clase Mailable `VerificationMail` para despachar el correo usando `Mail::to()->send()`.
4. El sistema retorna un Token de autenticación (Sanctum) por si el frontend decide dejar al usuario en la sesión, aunque restringido por el estado de cuenta.

### B. Bloqueo en el Login (`loginUser`)
Al intentar iniciar sesión:
1. El servicio revisa el campo `estado_cuenta`.
2. Si es `'BLOQUEADO'`, lanza excepción: *"Tu cuenta ha sido suspendida"*.
3. Si es `'PENDIENTE'`, lanza excepción: *"Tu cuenta no está verificada. Por favor, revisa tu correo electrónico"*. Evitando que acceda a la app.

### C. Verificación de Correo (`verifyEmail`)
1. El usuario introduce el correo y el código.
2. El sistema compara el `$code` con el `$user->codigo_verificacion`.
3. Si coincide, actualiza `estado_cuenta = 'VERIFICADO'` y pone `codigo_verificacion = null`.

### D. Reenvío de Código (`resendCode`)
- Genera un nuevo código de 6 dígitos, sobrescribe el anterior en la BD, y vuelve a disparar `VerificationMail`.

### E. Recuperación de Contraseña (Forgot Password)
- **Generación (`forgotPassword`)**: Genera un código de 6 dígitos, lo guarda en `token_recuperacion` y setea `expiracion_token` a `now()->addMinutes(15)` (15 minutos de validez). Envía el correo usando `PasswordResetMail`.
- **Validación (`verifyResetCode`)**: Revisa si el código existe y si `now()->greaterThan($user->expiracion_token)`. Si expiró, obliga al usuario a pedir otro.
- **Reinicio (`resetPassword`)**: Primero valida el código y expiración. Si está bien, actualiza el Hash de la nueva contraseña y vuelve `null` los tokens.

---

## 3. Endpoints de la API (`AuthController.php`) 

Las rutas asociadas a este flujo, ideales para conectar con el Frontend o probar en Postman, son:

| Método | Endpoint | Body Requerido (JSON) | Descripción |
| :--- | :--- | :--- | :--- |
| **POST** | `/api/v1/auth/register` | `nombre`, `email`, `password` | Crea la cuenta y envía el correo OTP. |
| **POST** | `/api/v1/auth/login` | `email`, `password` | Falla si el estado es PENDIENTE. |
| **POST** | `/api/v1/auth/verify-email` | `email`, `codigo_verificacion` | Activa la cuenta (pasa a VERIFICADO). |
| **POST** | `/api/v1/auth/resend-code` | `email` | Reenvía el código de activación OTP. |
| **POST** | `/api/v1/auth/forgot-password`| `email` | Envía el correo de recuperación. |
| **POST** | `/api/v1/auth/verify-reset` | `email`, `codigo` | Valida si el código de 15 min es correcto. |
| **POST** | `/api/v1/auth/reset-password` | `email`, `codigo`, `password` | Cambia la contraseña permanentemente. |

---

## 4. Configuración SMTP (`.env`) 
Para que el servidor de correos funcione, la app utiliza el proveedor SMTP de Google (Gmail). Los datos clave en el `.env` son:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME="ecoscanproyecto@gmail.com"
MAIL_PASSWORD="[CONTRASEÑA_DE_APLICACION]"
MAIL_ENCRYPTION=ssl
```
*(Nota: Para evitar errores en modo desarrollo local si no hay internet o si el puerto está bloqueado, el código de registro ignora silenciosamente los errores de correo temporalmente, pero guarda el código en BD).*
