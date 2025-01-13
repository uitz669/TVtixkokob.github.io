<?php

// Ruta del archivo .m3u
$file = 'playlist.m3u';

// Verificar si el archivo existe
if (file_exists($file)) {
    // Leer el archivo
    $lines = file($file, FILE_IGNORE_NEW_LINES);
    $channels = [];

    // Variables para almacenar los detalles de cada canal
    $current_channel = null;

    // Procesar el archivo línea por línea
    foreach ($lines as $line) {
        if (empty($line)) {
            continue;
        }

        // Si la línea contiene información del canal (tvg-id, tvg-logo, etc.)
        if (strpos($line, '#EXTINF') === 0) {
            // Extraer los metadatos del canal utilizando expresiones regulares
            preg_match('/#EXTINF:-1 tvg-id="([^"]+)" tvg-logo="([^"]+)" group-title="([^"]+)",(.+)/', $line, $matches);

            if ($matches) {
                // Almacenar los metadatos
                $current_channel = [
                    'id' => $matches[1],            // ID del canal
                    'logo' => $matches[2],          // Logo del canal
                    'group' => $matches[3],         // Grupo del canal (por ejemplo, "General", "Noticias")
                    'title' => trim($matches[4]),   // Nombre del canal
                ];
            }
        } elseif ($current_channel && !empty($line)) {
            // Si la siguiente línea es una URL (stream del canal)
            $current_channel['url'] = $line;
            $channels[] = $current_channel;
            $current_channel = null; // Restablecer para el siguiente canal
        }
    }
} else {
    $channels = [];
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TELEVISION TECNO TIX</title>
    <link rel="stylesheet" href="estilos.css">
    <!-- Incluir HLS.js -->
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

    <!-- SDK de Facebook -->
    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/es_LA/sdk.js#xfbml=1&version=v16.0"></script>
</head>
<body>

<div class="menu-container">
    <a href="principal_canales.php" class="modern-button">MENU PRINCIPAL</a>
    <img id="logo" src="tecnotix.png" alt="Logo TELEVISION TECNO TIX">
</div>
<h1>TELEVISION TECNO TIX</h1>

<!-- Contenedor principal (Reproductor + Lista de canales) -->
<div class="main-container">
    <!-- Reproductor de video -->
    <video id="video-player" controls>
        Tu navegador no soporta el reproductor de video.
    </video>

    <!-- Contenedor de la lista de canales -->
    <div class="channel-list-container">
        <!-- Filtro de búsqueda -->
        <input type="text" id="search-bar" placeholder="Buscar canales...">
        
        <!-- Lista de canales generada por PHP -->
        <ul class="channel-list">
            <?php foreach ($channels as $channel): ?>
                <li class="channel-item" data-group="<?php echo strtolower($channel['group']); ?>">
                    <a href="#" class="play-channel" data-url="<?php echo $channel['url']; ?>" data-logo="<?php echo $channel['logo']; ?>" data-title="<?php echo htmlspecialchars($channel['title']); ?>">
                        <img src="<?php echo $channel['logo']; ?>" alt="<?php echo htmlspecialchars($channel['title']); ?> Logo">
                        <?php echo htmlspecialchars($channel['title']); ?> (<?php echo htmlspecialchars($channel['group']); ?>)
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<!-- Sección de comentarios de Facebook -->
<div id="fb-comments">
    <div class="fb-comments" 
         data-href="https://tu-sitio-web.com" 
         data-width="800" 
         data-numposts="5">
    </div>
</div>

<script>
    // Obtener el reproductor de video
    var video = document.getElementById('video-player');
    
    // Obtener todos los enlaces de los canales
    var channels = document.querySelectorAll('.play-channel');
    var channelItems = document.querySelectorAll('.channel-item');

    // Filtrar canales por búsqueda
    document.getElementById('search-bar').addEventListener('input', function(e) {
        var searchTerm = e.target.value.toLowerCase();  // Obtener el término de búsqueda
        
        channelItems.forEach(function(item) {
            var channelName = item.querySelector('a').textContent.toLowerCase(); // Nombre del canal
            if (channelName.includes(searchTerm)) {
                item.style.display = '';  // Mostrar el canal
            } else {
                item.style.display = 'none';  // Ocultar el canal
            }
        });
    });

    // Cuando el usuario hace clic en un canal, reproducirlo
    channels.forEach(function(channel) {
        channel.addEventListener('click', function(e) {
            e.preventDefault();  // Evita la acción por defecto de los enlaces

            var url = channel.getAttribute('data-url');  // Obtener la URL del canal
            var logo = channel.getAttribute('data-logo'); // Obtener el logo del canal
            var title = channel.getAttribute('data-title'); // Obtener el título del canal

            // Cambiar el título del canal en la página
            document.title = title;

            // Si el navegador soporta HLS.js
            if (Hls.isSupported()) {
                var hls = new Hls();
                hls.loadSource(url); // Cargar el stream del canal
                hls.attachMedia(video);  // Asignar el stream al reproductor
                hls.on(Hls.Events.MANIFEST_PARSED, function () {
                    video.play();  // Reproducir el canal
                });
            }
            // Si el navegador soporta HLS nativamente (Safari)
            else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                video.src = url;
                video.play();
            }
        });
    });
</script>

</body>
</html>
