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
- `/carrito`, `/checkout`: flujo de compra demo
- `/admin`: dashboard de ventas y memoria
- `/admin/fotos`: carga por lotes, marca de agua y control de descargas
- `/admin/usuarios`: gestión de usuarios, estados, roles y permisos

Si la base ya estaba instalada, importa únicamente `database/update_usuarios_roles.sql`.
Para habilitar login, pedidos completos y CTA configurable, importa además `database/update_login_pedidos_cta.sql`. Luego ejecuta `php database/create_admin.php` para crear o restablecer el administrador sin publicar credenciales.
Para habilitar marca de agua por texto o imagen, importa `database/update_watermark_security.sql`.

## Seguridad de archivos

Los originales se guardan en `storage/originals`, bloqueado por `.htaccess`. Solo `/descarga` los entrega cuando existe un pedido pagado, el token coincide y la foto tiene la descarga habilitada. Las vistas previas son copias redimensionadas con marca de agua. Para producción agrega autenticación al panel y sustituye el pago demo por Webpay, Mercado Pago o Stripe con webhook verificado.
