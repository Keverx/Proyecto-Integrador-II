#  Documentación de Cambios Backend: EcoScan (Módulos Familias y Recompensas)

Este documento detalla todos los cambios arquitectónicos, estructurales y lógicos implementados en el backend para la gestión de familias y la tienda de recompensas. Está diseñado para que cualquier miembro del equipo (especialmente para el desarrollo del Dashboard o el Frontend) comprenda la estructura actual de la base de datos y cómo interactúan las APIs.

---

## 1. El Concepto Core: "Opción 3 - Metas Grupales" 

El cambio más importante a nivel de reglas de negocio es que **los grupos (familias) no tienen una billetera de puntos que se gasta**.
En su lugar, la lógica de los grupos funciona como un **sistema de logros o metas históricas**:
1. Cada vez que un usuario recicla, sus puntos se suman a su billetera personal y, al mismo tiempo, contribuyen al récord histórico de la familia (siempre y cuando los puntos se hayan obtenido *después* de que el usuario se uniera a esa familia).
2. Cuando la familia alcanza la cantidad de puntos de una Meta Grupal (ej. 5,000 puntos para la Medalla de Bronce), el Administrador del grupo puede "reclamar" esa meta.
3. Reclamar una meta **cuesta 0 puntos** para la familia. Esto permite que el récord histórico siga subiendo y puedan desbloquear la siguiente meta (ej. 20,000 puntos para un Vale).

---

## 2. Cambios Estructurales en la Base de Datos (Migraciones)

El archivo principal modificado es `2026_04_30_000000_create_ecoscan_v2_tables.php`. Aquí están las modificaciones clave a replicar:

### A. Tabla `grupos` (Familias)
- Pasamos de tener columnas que guardaban puntos estáticos a una estructura más limpia.
- **Columnas clave:** `id_grupo`, `nombre_grupo`, `fecha_creacion`.
- *(Nota: No hay columna de puntos grupales aquí; los puntos se calculan dinámicamente "al vuelo" sumando las transacciones de los miembros).*

### B. Tabla `usuario_grupos` (Pivot / Relación)
- **Columnas clave:** `id_usuario`, `id_grupo`, `rol_en_grupo` (puede ser `'ADMIN_GRUPO'` o `'MIEMBRO'`), `fecha_union`.
- `fecha_union` es crítica porque solo los puntos que el usuario genere *después* de esta fecha cuentan para la familia.

### C. Tabla `premios_incentivos` (Catálogo)
- Añadimos la columna estandarizada `categoria`.
- **Columnas clave:** `id_premio`, `nombre_premio`, `costo_puntos`, `tipo_premio` (`'INDIVIDUAL'` o `'GRUPAL'`), `stock_disponible`, **`categoria`** (VARCHAR 50).
- La columna `categoria` puede contener valores como `'comida'`, `'entretenimiento'`, `'logros'`, `'compras'`. Esto permite que el Frontend asigne iconos y colores dinámicos de forma desacoplada y limpia.

### D. Tabla `canjes` (Historial de Reclamaciones)
- Esta tabla unificada ahora maneja tanto los reclamos personales como los logros familiares.
- **Columnas clave:** `id_canje`, `id_usuario` (quién hizo el clic), `id_premio`, `cuenta_origen` (`'PERSONAL'` o `'GRUPAL'`), `id_grupo_afectado` (nulo si es personal, ID del grupo si es una meta familiar), `puntos_gastados` (costará el valor del premio si es personal, y `0` si es grupal).

### E. Tabla `transacciones_puntos`
- Sigue siendo la única fuente de la verdad para el saldo del usuario.
- Movimientos: `'INGRESO'` (por reciclar) y `'EGRESO'` (por canjear premios individuales).

---

## 3. Lógica Clave en los Modelos de Laravel 

### Modelo `User` (Cálculo dinámico de Puntos Personales)
En `User.php`, los puntos del usuario no son una columna estática en la tabla `usuarios`. Se calculan al vuelo mediante un "Accessor":
```php
public function getPuntosAttribute()
{
    $ingresos = $this->transacciones()->where('tipo_movimiento', 'INGRESO')->sum('monto');
    $egresos = $this->transacciones()->whereIn('tipo_movimiento', ['EGRESO', 'PENALIZACION'])->sum('monto');
    return $ingresos - $egresos;
}
```
*Por tanto, cuando se hace un canje individual, el backend solo inserta un registro de `EGRESO` y el saldo del usuario baja automáticamente.*

---

## 4. Controladores y APIs Implementadas 

### A. `FamilyController` (Grupos)
- **`POST /store`**: Crea un grupo, genera un código de invitación aleatorio (ej. `A1B2C3`) de 6 caracteres y asigna al creador el rol de `'ADMIN_GRUPO'`.
- **`POST /join`**: El usuario manda el `codigo_invitacion` y se le asigna el rol de `'MIEMBRO'`. Valida que no esté repetido.
- **`GET /show/{id}`**: Retorna los detalles del grupo y un arreglo `ranking`. El controlador hace una suma dinámica de los `'INGRESO'` de cada miembro desde su `fecha_union`. También retorna `puntos_totales` (la suma de todos los aportes del ranking).
- **`POST /leave/{id}`**: Permite a un usuario salir. Si es el último miembro, el grupo se elimina de la BD.
- **`POST /removeMember/{id}/{userId}`**: Permite al `'ADMIN_GRUPO'` expulsar a un miembro específico.

### B. `RewardController` (Premios y Metas)
- **`GET /index`**: Devuelve los premios separados en dos arrays (`individuales` y `grupales`) leyendo el campo `tipo_premio`. Solo muestra los de stock > 0.
- **`POST /canjear`**:
  - **Individual**: Revisa si el `$user->puntos` alcanza. Inserta en `canjes` y crea un `EGRESO` en `transacciones_puntos`.
  - **Grupal**: Exige que el `id_grupo_afectado` sea enviado, valida que el usuario sea el `ADMIN_GRUPO`, calcula dinámicamente si la suma histórica de todos los miembros supera el `costo_puntos` del premio. Valida en la tabla `canjes` que la familia no haya reclamado esta meta antes. Inserta en `canjes` con costo `0`. Disminuye stock. Todo bajo transacciones DB.
- **`GET /mis-canjes`**: Retorna `individuales` (filtrando por `id_usuario`) y `grupales` (buscando todos los canjes hechos por cualquiera de los IDs de familias a las que pertenece el usuario actual).

---

## 5. Seeders para Pruebas (Datos Iniciales) 

Para tener el entorno de pruebas rápido:
- **`DatabaseSeeder`**: Inserta los Tipos de Residuos, un Tacho de prueba y luego invoca al `RewardSeeder`.
- **`RewardSeeder`**: Define 6 premios categorizados, 4 de ellos `'INDIVIDUAL'` y 2 `'GRUPAL'`. Al ejecutar `php artisan migrate:fresh --seed`, la base de datos queda totalmente lista y sin datos duplicados usando métodos idempotentes (`firstOrCreate`).
