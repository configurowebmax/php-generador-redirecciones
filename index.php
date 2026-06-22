<?php
/**
 * Generador de Redirecciones .htaccess
 * Genera reglas de redirección para Apache (.htaccess)
 */
header('Content-Type: text/html; charset=utf-8');

$tipo = $_POST['tipo'] ?? '301';
$formato = $_POST['formato'] ?? 'redirect';
$reglas = [];
$codigo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lineas = trim($_POST['urls'] ?? '');
    if ($lineas !== '') {
        $filas = array_filter(array_map('trim', explode("\n", $lineas)));

        foreach ($filas as $fila) {
            $partes = preg_split('/[\s,;|]+/', $fila, 2);
            if (count($partes) === 2) {
                $origen = trim($partes[0]);
                $destino = trim($partes[1]);

                // Limpiar las URLs
                $origen = '/' . ltrim(parse_url($origen, PHP_URL_PATH) ?: $origen, '/');
                if (!preg_match('/^https?:\/\//', $destino)) {
                    $destino = '/' . ltrim($destino, '/');
                }

                $reglas[] = ['origen' => $origen, 'destino' => $destino];
            }
        }

        if (!empty($reglas)) {
            $lineasCodigo = ["# Redirecciones generadas por ConfiguroWeb", "# Fecha: " . date('Y-m-d H:i'), ""];

            if ($formato === 'rewriterule') {
                $lineasCodigo[] = "RewriteEngine On";
                $lineasCodigo[] = "";
                foreach ($reglas as $r) {
                    $patron = ltrim($r['origen'], '/');
                    $patron = preg_quote($patron, '/');
                    // Convertir \* en (.*)
                    $patron = str_replace('\\*', '(.*)', $patron);
                    $flag = $tipo === '301' ? 'R=301,L' : 'R=302,L';
                    $lineasCodigo[] = "RewriteRule ^{$patron}$ {$r['destino']} [{$flag}]";
                }
            } else {
                foreach ($reglas as $r) {
                    $lineasCodigo[] = "Redirect {$tipo} {$r['origen']} {$r['destino']}";
                }
            }

            $codigo = implode("\n", $lineasCodigo);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Generador de Redirecciones .htaccess Online | ConfiguroWeb</title>
<meta name="description" content="Genera reglas de redirección .htaccess (301 y 302) para Apache online gratis. Redirect y RewriteRule listas para copiar.">
<meta name="keywords" content="generador redirecciones, htaccess, redirect 301, redirect 302, rewriterule, apache">
<meta property="og:type" content="website">
<meta property="og:title" content="Generador de Redirecciones .htaccess Online">
<meta property="og:description" content="Genera reglas de redirección .htaccess (301 y 302) para Apache online gratis.">
<link rel="canonical" href="https://demoscweb.com/github/php-generador-redirecciones/">
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"WebApplication","name":"Generador de Redirecciones","applicationCategory":"UtilitiesApplication","operatingSystem":"Any","offers":{"@type":"Offer","price":"0","priceCurrency":"USD"},"author":{"@type":"Person","name":"ConfiguroWeb","url":"https://configuroweb.com"}}
</script>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header>
  <h1>↩️ Generador de Redirecciones .htaccess</h1>
  <p class="subtitle">Crea reglas Redirect y RewriteRule para Apache</p>
</header>
<main>
  <form method="POST">
    <label for="urls">URLs (una por línea: origen → destino)</label>
    <textarea name="urls" id="urls" rows="6" placeholder="/pagina-vieja https://midominio.com/pagina-nueva
/blog/post-antiguo /blog/post-nuevo
/categoria/* /nueva-categoria/" required><?php echo htmlspecialchars($_POST['urls'] ?? ''); ?></textarea>
    <p style="color:var(--muted);font-size:.8rem;margin-top:.3rem">Separa origen y destino con espacio, coma o tabulación.</p>

    <label for="tipo">Tipo de redirección</label>
    <select name="tipo" id="tipo">
      <option value="301" <?php if($tipo==='301') echo 'selected'; ?>>301 — Permanente (SEO recomendado)</option>
      <option value="302" <?php if($tipo==='302') echo 'selected'; ?>>302 — Temporal</option>
    </select>

    <label for="formato">Formato de salida</label>
    <select name="formato" id="formato">
      <option value="redirect" <?php if($formato==='redirect') echo 'selected'; ?>>Redirect (simple)</option>
      <option value="rewriterule" <?php if($formato==='rewriterule') echo 'selected'; ?>>RewriteRule (avanzado)</option>
    </select>

    <button type="submit" class="btn-primary">↩️ Generar Reglas</button>
  </form>

  <?php if ($codigo !== ''): ?>
  <div class="resultados" style="margin-top:1.5rem">
    <h2 style="margin-bottom:.5rem;font-size:1.1rem">Código .htaccess generado (<?php echo count($reglas); ?> reglas)</h2>
    <div style="position:relative">
      <pre style="background:#0f172a;padding:1rem;border-radius:var(--radius);font-family:'Cascadia Code',Consolas,monospace;font-size:.85rem;color:#93c5fd;overflow-x:auto;white-space:pre;line-height:1.5"><code><?php echo htmlspecialchars($codigo); ?></code></pre>
    </div>
    <p style="color:var(--muted);font-size:.8rem;margin-top:.5rem">📋 Copia este código y pégalo al inicio de tu archivo .htaccess</p>
  </div>
  <?php endif; ?>

  <section class="info">
    <h2>¿Cuándo usar cada tipo?</h2>
    <p><strong>Redirect 301 (Permanente):</strong> Cuando cambias una URL para siempre. Transfiere el SEO de la URL vieja a la nueva. Ideal para migraciones y rediseños.</p>
    <p><strong>Redirect 302 (Temporal):</strong> Cuando la URL original volverá a funcionar. Usado en mantenimiento o promociones temporales.</p>
    <p><strong>Redirect (simple):</strong> Sintaxis básica de Apache. Funciona para la mayoría de los casos.</p>
    <p><strong>RewriteRule (avanzado):</strong> Más flexible, permite patrones regex y condiciones complejas.</p>
  </section>
</main>
<footer>
  <p>Desarrollado por <a href="https://configuroweb.com" target="_blank">ConfiguroWeb</a> ·
     <a href="https://appscweb.com/citas/" target="_blank">Sistema de Citas</a> ·
     <a href="https://appscweb.com/negocios/" target="_blank">Gestión de Negocios</a></p>
  <p>&copy; <?php echo date('Y'); ?> ConfiguroWeb</p>
</footer>
<script src="assets/script.js"></script>
</body>
</html>
