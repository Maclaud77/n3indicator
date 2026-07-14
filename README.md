# N3 Indicator — Plugin para GLPI

Plugin para GLPI 11.0.x que identifica visualmente los tickets derivados a soporte de Nivel 3 (N3).

## ¿Qué hace?

- **Badge N3** en el listado de tickets con el color del grupo asignado
- **Banner visual** dentro del ticket indicando escalación a soporte N3
- **Página de configuración** para definir qué grupos son N3 y asignar un color a cada uno
- **Colores dinámicos** — cada grupo N3 puede tener su propio color identificador
- **Activar/desactivar** grupos sin eliminar la configuración

## Requisitos

| Componente | Versión |
|---|---|
| GLPI | 11.0.0 – 11.9.9 |
| PHP | 8.1+ |

## Instalación

### Opción 1 — Manual
1. Descargar el ZIP desde [Releases](https://github.com/Maclaud77/n3indicator/releases)
2. Descomprimir en `/glpi/plugins/n3indicator/`
3. Ir a **Configuración → Plugins**
4. Hacer clic en **Instalar** y luego **Activar**

### Opción 2 — Marketplace GLPI
1. Ir a **Configuración → Plugins → Mercado**
2. Buscar **N3 Indicator**
3. Hacer clic en **Instalar**

## Configuración

1. Ir a **Configuración → Plugins**
2. Hacer clic en el ícono de llave (🔧) del N3 Indicator
3. Agregar los grupos que corresponden a soporte N3
4. Asignar un color identificador a cada grupo

## Contexto

Desarrollado para el **SSMSO (Servicio de Salud Metropolitano Sur Oriente)** como solución para identificar tickets escalados al equipo URCIT. El flujo consiste en derivar tickets mediante **"Promocionar a Solicitud"** de GLPI, creando un ticket hijo asignado al grupo N3.

## Estructura



## Licencia

[GPLv2+](LICENSE)

## Autor

**SSMSO - URCIT** — [@Maclaud77](https://github.com/Maclaud77)
