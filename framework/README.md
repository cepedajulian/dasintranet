# DasIntranet Framework

**Versión 1.0.2** · **PHP 8.4** · **Composer / PSR-4**

DasIntranet Framework es un microframework PHP orientado a aplicaciones web y APIs sencillas. Ofrece enrutamiento HTTP, controladores, vistas con layouts, acceso a MariaDB/MySQL mediante PDO y clientes HTTP basados en cURL.

## Novedades de la versión 1.0.2

- **`ApiFree` y sesión web:** reenvía automáticamente el identificador de la sesión PHP activa a la misma instancia del servidor, únicamente por HTTPS y con un destino confiable. No envía cookies a servicios externos. Se libera el bloqueo de la sesión durante cURL y se restablece después, incluso ante errores.
- **`ApiFree` y body:** conserva literalmente los cuerpos enviados como string; mantiene el comportamiento heredado para arrays; añade modos explícitos `json` y `form`. Permite recuperar tanto la respuesta decodificada en `$response` como el cuerpo exacto en `$rawBody`.
- **Respuesta HTTP:** `ApiFree` conserva el código de estado y reenvía únicamente determinadas cabeceras seguras, con `$forwardResponse = true` por defecto para compatibilidad. Nunca reenvía `Set-Cookie` ni `Content-Length`.
- **Sin cambios en el orden de argumentos:** `__construct($url, $method, $body)` y `exec($url, $method, $body)` mantienen su contrato posicional.
- **Pruebas:** `php tests/apifree_compat.php` comprueba la compatibilidad sin depender de una API externa.

# DasIntranet Framework — versión 1.0.1 optimizada para PHP 8.4

Microframework PHP sin dependencia de Laravel, Symfony ni PSR-7.

## Qué se actualizó

- **PSR-4:** archivos de clases con nombres de mayúsculas/minúsculas correctos (`App.php`, `Db.php`, etc.).
- **PSR-12:** formato de clases, imports, llaves, sangría y espaciado homogéneos.
- **Router:** elimina los parámetros de query string al resolver la ruta, escapa la parte literal de los patrones, acepta parámetros de segmento y maneja 404 sin reflejar entradas inseguras.
- **Vistas:** `extend()` incluye rutas reales de `layouts/appHead.php`, vista y `layouts/appFooter.php`. Evita path traversal y limpia correctamente buffers si hay excepciones.
- **Db:** prepared statements nativos, UTF-8 `utf8mb4`, validación de identificadores, consulta de lectura sin transacción y función explícita `transaction()` para escritura atómica.
- **HTTP (`Api`, `ApiFree`):** adaptador compartido, validación de certificados HTTPS, timeout de conexión/respuesta, códigos HTTP reales en `$status` y excepciones en vez de `die()`.

## Requisitos

- PHP 8.4
- Composer 2
- `ext-pdo` (y `pdo_mysql` si se utiliza `Db` con MariaDB)
- `ext-curl` para `Api` y `ApiFree`

## Inicialización de aplicaciones (`Loader`)

El cargador pertenece ahora a este paquete (`src/Loader.php`). No se requiere
`app/loader.php` en cada aplicación.

En `public/index.php`, después de incluir `vendor/autoload.php`:

```php
\Dasintranet\Framework\Loader::run(dirname(__DIR__) . '/app');
```

`Loader::boot($appDirectory)` registra vistas, carga opcionalmente
`app/controllers/functions.php` y registra los archivos `app/routes/*.php` en
orden alfabético, **sin** despachar la petición. `Loader::run()` hace lo mismo
y además llama a `App::run()`.

Los controladores se cargan mediante el mapeo PSR-4 de la aplicación en
`composer.json` (`App\Controllers\` → `app/controllers/`). No se usa `glob`
para incluir las clases manualmente. El directorio de aplicación debe existir;
si no existe se lanza `InvalidArgumentException`.

## Uso manual (sin Loader)

```php
use Dasintranet\Framework\App;
use Dasintranet\Framework\Db;
use Dasintranet\Framework\View;

App::setViews(__DIR__ . '/app/views');
App::get('/usuarios/{id}', static fn (string $id): array => ['id' => $id]);
App::run();

// Transacciones explícitas, solo si hacen falta:
$db = new Db();
$db->transaction(static function (Db $db): void {
    $db->query('UPDATE usuarios SET activo = ? WHERE id = ?', [1, 10]);
    $db->query('INSERT INTO auditoria (descripcion) VALUES (?)', ['Activación']);
});
```

**Parámetros SQL:** usar `?` o `:nombre` para los valores, nunca concatenar datos de usuarios.
`Db::find('tabla', 'campo', $valor)` valida nombres de tabla/columna y parametriza el valor.

Para compartir la sesión PHP con un servicio interno (opcional):

```php
$api = new Dasintranet\Framework\ApiFree();
$api->shareSession = true; // Solo HTTPS hacia $_SERVER['SERVER_NAME']
$resultado = $api->exec('https://mi-servidor.example/api/endpoint');
```

Las cookies **nunca** se reenvían por defecto. `Api::getAccess()` exige HTTPS al transmitir credenciales Basic.

## Pruebas rápidas

Descomprimir framework y aplicación como carpetas hermanas, y ejecutar:

```bash
php framework-1.0/tests/smoke.php
```

El entorno de pruebas debe contar con PHP 8.4; las comprobaciones sin base de datos ni red
no necesitan Composer. La integración completa requiere una instalación real de MariaDB,
la extensión PDO MySQL, la extensión cURL y el autoloader generado por Composer.

Ver `CAMBIOS.md` en el ZIP conjunto para limitaciones y diferencias de compatibilidad.
