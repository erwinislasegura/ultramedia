# Ultra Media Digital

Tienda de fotografía deportiva en PHP 8.1+, MySQL y MVC, lista para cPanel.

## Instalación en cPanel

1. Sube todo el contenido a `public_html` (el `index.php` queda en la raíz).
2. Crea una base de datos y usuario MySQL desde cPanel.
3. Importa `database/schema.sql` y luego `database/demo.sql` desde phpMyAdmin.
4. Edita `config/config.php` con las credenciales o define `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` y `APP_URL`.
5. Usa PHP 8.1 o superior y habilita PDO MySQL, GD, fileinfo y mod_rewrite.
6. Da permiso de escritura a `storage/originals` y `storage/previews`.

### Permisos en XAMPP para macOS

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/ultramedia
sudo chown -R daemon:daemon storage
sudo chmod -R 775 storage
```

Después reinicia Apache desde XAMPP. El usuario del servidor debe poder escribir en `storage/originals` y `storage/previews`.

## Rutas

- `/`: tienda con búsqueda y cuadrícula 4 × 3
- `/foto?id=1`: detalle
- `/carrito`, `/checkout`: flujo de compra conectado a Flow
- `/admin`: dashboard de ventas y memoria
- `/admin/fotos`: carga por lotes, marca de agua y control de descargas
- `/admin/usuarios`: gestión de usuarios, estados, roles y permisos

Si la base ya estaba instalada, importa únicamente `database/update_usuarios_roles.sql`.
Para habilitar login, pedidos completos y CTA configurable, importa además `database/update_login_pedidos_cta.sql`. Luego ejecuta `php database/create_admin.php` para crear o restablecer el administrador sin publicar credenciales.
Para habilitar marca de agua por texto o imagen, importa `database/update_watermark_security.sql`.
Para habilitar Flow en una base existente, importa `database/update_flow_payments.sql`.
Para administrar Flow desde el panel, importa también `database/update_flow_settings.sql` y abre `/admin/flow`.
Para habilitar cuentas de clientes y venta de sets completos, importa `database/update_customers_sets.sql`.

## Configuración de Flow

1. Crea primero una cuenta de prueba en `sandbox.flow.cl` y copia su API Key y Secret Key.
2. Configura `APP_URL` con la URL pública HTTPS exacta del proyecto. Flow no puede confirmar pagos contra `localhost`.
3. Ingresa a **Panel → Pasarela Flow**, selecciona Sandbox o Producción e introduce la API Key y Secret Key.

```text
APP_URL=https://tudominio.cl
APP_KEY=una_clave_privada_larga_y_aleatoria
```

El ambiente se cambia desde el panel. La Secret Key queda cifrada y no vuelve a mostrarse. `APP_KEY` protege ese cifrado; si no se define, el sistema deriva una clave desde la conexión de base de datos. PHP debe tener cURL, OpenSSL y ZipArchive habilitados. ZipArchive genera la descarga única de cada set completo. Las URLs de confirmación y retorno se generan automáticamente desde `APP_URL`.

## Portal de clientes y modalidades de venta

- `/mi-cuenta/login`: acceso de clientes.
- `/mi-cuenta`: pedidos, estados y cambio de contraseña.
- En checkout el comprador puede crear una cuenta opcionalmente.
- Cada lote subido crea un set y permite activar venta individual, set completo o ambas modalidades.
- Los pedidos pagados habilitan la foto original o un ZIP con todas las fotografías del set.

## Seguridad de archivos

Los originales se guardan en `storage/originals`, bloqueado por `.htaccess`. Solo `/descarga` los entrega cuando Flow confirmó el pedido como pagado, el token local coincide y la foto tiene la descarga habilitada. Las vistas previas son copias redimensionadas con marca de agua. El callback nunca confía en datos enviados por el navegador: vuelve a consultar el estado firmado en la API de Flow y valida pedido, monto y moneda.
