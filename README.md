# DasIntranet Framework

**Versión 1.0.2** · **PHP 8.4** · **Composer / PSR-4**

DasIntranet Framework es un microframework PHP orientado a aplicaciones web y APIs sencillas. Ofrece enrutamiento HTTP, controladores, vistas con layouts, acceso a MariaDB/MySQL mediante PDO y clientes HTTP basados en cURL.

## Novedades de la versión 1.0.2

- **`ApiFree` y sesión web:** reenvía automáticamente el identificador de la sesión PHP activa a la misma instancia del servidor, únicamente por HTTPS y con un destino confiable. No envía cookies a servicios externos. Se libera el bloqueo de la sesión durante cURL y se restablece después, incluso ante errores.
- **`ApiFree` y body:** conserva literalmente los cuerpos enviados como string; mantiene el comportamiento heredado para arrays; añade modos explícitos `json` y `form`. Permite recuperar tanto la respuesta decodificada en `$response` como el cuerpo exacto en `$rawBody`.
- **Respuesta HTTP:** `ApiFree` conserva el código de estado y reenvía únicamente determinadas cabeceras seguras, con `$forwardResponse = true` por defecto para compatibilidad. Nunca reenvía `Set-Cookie` ni `Content-Length`.
- **Sin cambios en el orden de argumentos:** `__construct($url, $method, $body)` y `exec($url, $method, $body)` mantienen su contrato posicional.
- **Pruebas:** `php tests/apifree_compat.php` comprueba la compatibilidad sin depender de una API externa.

# DasIntranet — Framework PHP 1.0.1

Microframework PHP para desarrollar aplicaciones web y APIs pequeñas sin depender de Laravel ni Symfony. Incluye enrutamiento, controladores, vistas, acceso a MariaDB/MySQL mediante PDO y clientes HTTP con cURL.

**Versión:** `1.0.1` · **PHP:** `^8.4` · **Autoloading:** PSR-4

> Este repositorio contiene una aplicación de ejemplo y el framework en la carpeta `framework/`. Se utiliza Composer para cargar las clases; no es necesario copiar `src/` manualmente a `vendor/`.

## Novedades de la versión 1.0.1

- **`Loader` incorporado al framework:** sustituye el archivo `app/loader.php` y registra rutas, directorio de vistas y funciones auxiliares.
- **PSR-4:** nombres de archivos de clase corregidos y carga automática de controladores con Composer.
- **Estilo PSR-12:** organización y formato homogéneos en las clases revisadas.
- **Enrutador mejorado:** admite GET, POST, PUT, PATCH y DELETE; rutas parametrizadas; ignora el *query string* al buscar coincidencias y evita interpretar caracteres especiales de las rutas literales como expresiones regulares.
- **Vistas:** se corrige `View::extend()`, se valida la ubicación de los archivos y se limpian los búferes de salida cuando se produce una excepción.
- **Base de datos:** consultas preparadas nativas, conexión `utf8mb4`, validación de identificadores y transacciones explícitas mediante `Db::transaction()`.
- **Clientes HTTP:** `Api` y `ApiFree` comparten un cliente interno, verifican certificados TLS, utilizan tiempos de espera y exponen el estado HTTP mediante `$status`.
- **Seguridad:** las cookies de sesión no se envían a servicios externos de forma automática; CORS permanece desactivado salvo configuración explícita en la aplicación de ejemplo.
- **Ejemplo funcional:** se incluyen las rutas `/` para comprobar la instalación.

## Requisitos

- PHP **8.4** o superior, dentro de la restricción `^8.4` de Composer.
- Composer 2.
- Extensiones PHP `json` y `pdo`.
- `pdo_mysql` si se utiliza la clase `Db` con MariaDB/MySQL.
- `curl` si se utilizan las clases `Api` o `ApiFree`.
- MariaDB o MySQL **solo** si la aplicación necesita base de datos.

## Estructura del proyecto

```text
dasintranet/
├── app/
│   ├── controllers/
│   │   ├── HomeController.php
│   │   └── functions.php
│   ├── routes/
│   │   └── web.php
│   └── views/
│       ├── hello.php
│       ├── error404.php
│       └── layouts/
│           ├── appHead.php
│           └── appFooter.php
├── framework/
│   ├── composer.json
│   └── src/
│       ├── App.php
│       ├── Controller.php
│       ├── View.php
│       ├── Db.php
│       ├── Loader.php
│       ├── Api.php
│       ├── ApiFree.php
│       └── HttpClient.php
├── public/
│   ├── index.php
│   └── .htaccess
├── composer.json
├── set.php
└── vendor/               # Generado por Composer
```

## Instalación y ejecución local

Desde el directorio principal:

```bash
composer install
php -S 127.0.0.1:8000 -t public public/index.php
```

Abrí [http://127.0.0.1:8000/](http://127.0.0.1:8000/) o [http://127.0.0.1:8000/hello](http://127.0.0.1:8000/hello). Ambas rutas responden `Hello World!`.

El `composer.json` de la aplicación utiliza un repositorio local `path`:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "framework",
      "options": {
        "symlink": true,
        "versions": {
          "dasintranet/framework": "1.0.1"
        }
      }
    }
  ]
}
```

Composer administra las dependencias en `vendor/` y registra automáticamente las clases de `Dasintranet\Framework\` y `App\Controllers\`.

## Inicialización

En `public/index.php`, después de cargar la configuración y el autoloader de Composer:

```php
require_once dirname(__DIR__) . '/set.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

\Dasintranet\Framework\Loader::run(dirname(__DIR__) . '/app');
```

`Loader::run()` configura la aplicación y despacha la petición HTTP actual. Si necesitás registrar las rutas y las vistas **sin ejecutar** una petición, por ejemplo en un script de consola, usá:

```php
\Dasintranet\Framework\Loader::boot(dirname(__DIR__) . '/app');
```

`Loader` carga `app/controllers/functions.php` si existe y los archivos `app/routes/*.php` en orden alfabético. Los controladores se cargan por PSR-4, sin `require_once` manual.

## Rutas

Archivo `app/routes/web.php`:

```php
<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use Dasintranet\Framework\App;

App::get('/', [HomeController::class, 'hello']);
App::get('/hello', [HomeController::class, 'hello']);
App::get('/alumnos/{id}', static fn (string $id): array => ['id' => $id]);

// También existen App::post(), App::put(), App::patch() y App::delete().
```

Los parámetros entre llaves se pasan al *callback* de la ruta en orden. Si un controlador o *callback* devuelve un arreglo u objeto, `App::run()` lo serializa como JSON; si devuelve una cadena, la imprime directamente.

## Controladores y vistas

Archivo `app/controllers/HomeController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use Dasintranet\Framework\Controller;
use Dasintranet\Framework\View;

class HomeController extends Controller
{
    public static function hello(): string
    {
        return (new View())->render('hello');
    }
}
```

Para renderizar una vista y pasarle datos:

```php
$html = (new View())->render('alumnos.index', [
    'titulo' => 'Alumnos',
    'alumnos' => $alumnos,
]);
```

`View::render('alumnos.index')` busca `app/views/alumnos/index.php`. Para incluir los *layouts* `layouts/appHead.php` y `layouts/appFooter.php` alrededor de la vista:

```php
$html = (new View())->extend('alumnos.index', ['titulo' => 'Alumnos']);
```

Los valores enviados a las vistas se convierten en variables PHP mediante `extract(..., EXTR_SKIP)`. Escapá al imprimir datos provenientes de usuarios, por ejemplo con `htmlspecialchars($valor, ENT_QUOTES, 'UTF-8')`.

## Base de datos (PDO)

La clase `Db` utiliza los valores configurados en `set.php` (`DatabaseServer`, `Database`, `DatabaseUser`, `DatabasePassword`).

```php
use Dasintranet\Framework\Db;

$db = new Db();

$alumnos = $db->query(
    'SELECT id, apellido FROM alumnos WHERE activo = ?',
    [1]
); // array de objetos

$alumno = $db->find('alumnos', 123); // por id; objeto o false

$filasAfectadas = $db->query(
    'UPDATE alumnos SET activo = ? WHERE id = ?',
    [0, 123]
); // int
```

Para que varias escrituras se confirmen juntas, utilizá una transacción:

```php
$db->transaction(static function (Db $db): void {
    $db->query('UPDATE alumnos SET activo = ? WHERE id = ?', [1, 123]);
    $db->query(
        'INSERT INTO auditoria (alumno_id, accion) VALUES (?, ?)',
        [123, 'activacion']
    );
});
```

Si ocurre una excepción, la transacción revierte las operaciones. No se admiten transacciones anidadas. No concatenes valores suministrados por usuarios en las consultas SQL: usá parámetros preparados.

## Consumir APIs HTTP

Con token Bearer:

```php
use Dasintranet\Framework\Api;

$api = new Api('https://api.ejemplo.com/alumnos', 'GET', null, $token);

$datos = $api->response;
$codigoHttp = $api->status;
```

Sin token:

```php
use Dasintranet\Framework\ApiFree;

$api = new ApiFree('https://api.ejemplo.com/estado');

$datos = $api->response;
$codigoHttp = $api->status;
```

Las peticiones HTTP usan cURL y no siguen redirecciones automáticamente. Para obtener credenciales mediante `Api::getAccess()`, la URL debe utilizar HTTPS. `ApiFree::shareSession` está desactivado por defecto; solo permite reenviar una sesión activa a la misma máquina, identificada por `SERVER_NAME`, mediante HTTPS.

## Configuración web y seguridad

El **document root** del servidor debe apuntar a `public/`, nunca a la raíz del proyecto. En Nginx, el bloque de ubicación principal debe incluir una regla equivalente a:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

Esta regla se complementa con la configuración de PHP-FPM correspondiente. En Apache se puede utilizar el archivo `public/.htaccess` incluido.

La aplicación de ejemplo solo habilita CORS cuando la variable `CORS_ALLOWED_ORIGIN` coincide exactamente con el origen de la solicitud. Para producción, mantené `display_errors` desactivado y configurá los registros de errores del servidor.

### Diagnóstico rápido

- **404 del framework**: comprobá que la ruta exista en `app/routes/*.php` y que `Loader::run()` se ejecute.
- **404 del servidor web**: comprobá el document root (`public/`) y las reglas de reescritura.
- **Error 500**: verificá que exista `vendor/autoload.php`, que el servidor tenga las extensiones necesarias y que las variables de entorno estén configuradas.
- **Clase no encontrada**: comprobá el *namespace*, las mayúsculas/minúsculas del archivo y ejecutá `composer dump-autoload`.

## Migración desde la versión 1.0

1. Actualizá los archivos del framework y asegurate de conservar la estructura PSR-4 (`src/App.php`, `src/Db.php`, etc.).
2. Sustituí la inclusión de `app/loader.php` por `Dasintranet\Framework\Loader::run(...)` en `public/index.php`.
3. Eliminá las inclusiones manuales de controladores y verificá el mapeo `App\Controllers\` en el `composer.json` de la aplicación.
4. Ejecutá `composer install` y `composer dump-autoload` en la raíz del proyecto.
5. Revisá los puntos donde se capturan errores SQL o de vistas: ahora se lanzan excepciones en lugar de detener el proceso con `die()` o devolver silenciosamente un mensaje.
6. Revisá las integraciones que dependieran del envío automático de cookies mediante `ApiFree`; ahora está deshabilitado por seguridad.
7. Verificá consultas que dependieran de conversiones implícitas de PDO o de transacciones automáticas; utilizá `Db::transaction()` cuando necesites atomicidad.

**Compatibilidad:** se conserva la mayor parte de los métodos públicos (`App::get()`, `View::render()`, `View::extend()`, `Db::query()`, `Db::find()`, `Api`, `ApiFree`), pero la migración **no es totalmente transparente** debido a los cambios en errores, transacciones, CORS y cookies.

## Estándares y alcance

El proyecto utiliza autoloading **PSR-4** y adopta convenciones de estilo **PSR-12**. **No implementa PSR-7** (objetos HTTP Request/Response), PSR-15 (middleware) ni PSR-17 (fábricas HTTP). Estos estándares no son necesarios para utilizar las funcionalidades actuales.

## Licencia

El `composer.json` declara la licencia **MIT**.
