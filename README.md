# Expediatravels

Plataforma web y aplicación PWA para explorar, reservar y pagar experiencias turísticas en Oxapampa, Villa Rica, Pozuzo, Perené y Yanachaga.

## Objetivo

Centralizar la oferta turística de la región en una experiencia digital moderna con estética inspirada en iOS Travel Apps, Aspen UI y Bali Travel.

## Stack tecnológico

| Componente            | Tecnología                                      |
| --------------------- | ----------------------------------------------- |
| Frontend web/app      | HTML5, Tailwind CSS, JavaScript modular         |
| Backend               | PHP con arquitectura MVC                         |
| Base de datos         | MySQL                                           |
| Panel administrativo  | PHP + Tailwind + MySQL                          |
| Mapas y geolocalización | Mapbox GL JS                                  |
| Pasarelas de pago     | Izipay, PayPal, Culqi                           |
| Correos y notificaciones | PHPMailer + SMTP                             |
| Modo PWA              | Manifest + Service Worker                       |

## Estructura del proyecto

```text
expediatravels/
├── aplicacion/
│   ├── configuracion/
│   ├── controladores/
│   ├── modelos/
│   └── vistas/
├── sitio_web/
│   ├── recursos/
│   ├── estilos/
│   ├── scripts/
│   ├── explorar.php
│   ├── index.php
│   ├── paquete.php
│   └── perfil.php
├── administracion/
│   ├── destinos.php
│   ├── index.php
│   ├── paquetes.php
│   ├── reportes.php
│   └── usuarios.php
└── base_datos/
    ├── esquema.sql
    ├── datos_semilla.sql
    └── migraciones/
```

## Módulos principales

### 1. Sitio público

- **Home:** hero con imagen destacada, buscador y secciones dinámicas (destinos, tours recomendados, mapa interactivo).
- **Explorar:** filtros por destino, duración, precio y categoría.
- **Detalle de tour:** galería, itinerario, precios, reseñas y botón de reserva.
- **Carrito y checkout:** selección de fecha, cantidad de personas y pago en línea.
- **Cuenta:** historial de reservas, vouchers y datos personales.
- **Blog:** contenido sobre cultura, gastronomía y sostenibilidad.

### 2. Panel administrativo

- **Dashboard:** métricas de reservas, ingresos y visitas.
- **Gestión de contenidos:** CRUD de destinos (con coordenadas Mapbox), paquetes, usuarios y reseñas.
- **Configuraciones:** pasarelas de pago, políticas, horarios y redes sociales.
- **Reportes:** exportables en Excel o PDF.

### 3. Base de datos

Entidades principales:

- `usuarios`: id, nombre, correo, contraseña hash, rol, creado_en.
- `destinos`: id, nombre, descripción, lat, lon, imagen, región.
- `paquetes`: id, destino_id, nombre, resumen, itinerario, duración, precio, estado.
- `reservas`: id, usuario_id, paquete_id, fecha_reserva, cantidad_personas, total, estado, creado_en.
- `pagos`: id, reserva_id, método, monto, estado, fecha_pago.
- `resenas`: id, usuario_id, paquete_id, rating, comentario, fecha.

## Integraciones previstas

- **Mapbox:** visualización de tours geolocalizados y rutas.
- **Pasarelas de pago:** Izipay, PayPal y Culqi.
- **Correo automático:** confirmaciones de reserva y vouchers.
- **Redes sociales:** integración con Facebook, Instagram y TikTok.

## Contenido inicial

- Tour Perené – Catarata Bayoz, Velo de la Novia, Mariposario y paseo en bote.
- Tour Oxapampa – Tunqui Cueva, El Wharapo, Catarata Río Tigre y Parque Temático.
- Tour Pozuzo – Cascadas, cerveza artesanal, puente colgante y parque temático.
- Tour Villa Rica – Portal, Laguna El Oconal, ictioterapia, catación de café y Mirador La Cumbre.
- Tour Yanachaga – Caminatas, avistamiento de aves y gallito de las rocas.

## Datos de contacto

- **Agencia:** Expedia Travel
- **Dirección:** Jr. Bolívar 466 – Oxapampa
- **Celular:** +51 930 140 668
- **Correo:** info.expediatravell@gmail.com
- **Facebook:** Expedia Travel
- **Instagram:** @expediatravel.pe
- **TikTok:** @expediatravel.oxapampa

## Próximos pasos

1. Diseño UI/UX basado en las referencias seleccionadas.
2. Implementación del backend PHP MVC con MySQL.
3. Desarrollo del frontend responsivo con Mapbox GL JS.
4. Integración de pasarelas de pago.
5. Construcción del panel administrativo y habilitación PWA.
