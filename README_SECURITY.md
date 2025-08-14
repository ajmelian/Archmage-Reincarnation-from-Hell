# Seguridad avanzada (S34)

Este sprint añade **cabeceras de seguridad y CSP**, **CSRF reforzado**, **rehash automático de contraseñas** y **2FA TOTP**.

## Qué incluye
- **Migration 032**: columnas `twofa_secret`, `twofa_enabled`, `last_login_at`, `last_login_ip` en `users`.
- **Hook** `SecurityHeaders` con `Content-Security-Policy`, **HSTS**, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy` y `Permissions-Policy` (configurable en `application/config/security.php`).
- **2FA TOTP** (`TwoFA` + controlador `Twofa`) con páginas para activar, verificar en login y desactivar.
- **Rehash de contraseñas** (`Passwords`): si cambia `PASSWORD_DEFAULT` o tus opciones, se re‑hash en el **próximo login** exitoso.
- **CSRF**: ejemplo de config de producción con CSRF activado y expira a 300s (`application/config/production/config.php`).

## Integración en Auth (si no estaba)
Tras validar email/usuario + contraseña:
```php
// Rehash automático
$this->load->library('Passwords');
if (!$this->passwords->verifyAndRehash($user, $plainPassword)) { /* invalid */ }

// Paso 2FA
if (!empty($user['twofa_enabled'])) {
    $this->session->set_userdata('twofa_pending_user', $user['id']);
    redirect('twofa/login_step'); return;
}
// completar login normal
```

## Uso (2FA)
1. Ir a **/twofa** → Activar 2FA → escanear QR → introducir código de 6 dígitos.
2. En el siguiente login, si está activo, se pedirá el **código TOTP** en `/twofa/login_step`.

## CSP
Edita `application/config/security.php` y añade los CDNs exactos que uses en `script-src`/`style-src`/`img-src`/`font-src`.
Por defecto permite: `cdn.jsdelivr.net`, `fonts.googleapis.com`, `fonts.gstatic.com` y el generador de QR.

## CLI
```bash
php public/index.php securitycli twofa_setup <userId>
php public/index.php securitycli twofa_disable <userId>
php public/index.php securitycli show_csp
```

## Notas
- **HSTS** solo aplica si sirves vía HTTPS.
- Si usas plantillas con formularios, asegúrate de incluir `<?php echo form_open(); ?>` o tokens `_csrf` de CI3.
- Todo es determinista y sin IA.
