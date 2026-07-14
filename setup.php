<?php
define('PLUGIN_N3INDICATOR_VERSION', '1.0.0');
define('PLUGIN_N3INDICATOR_MIN_GLPI', '11.0.0');
define('PLUGIN_N3INDICATOR_MAX_GLPI', '11.9.9');

function plugin_init_n3indicator() {
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['n3indicator'] = true;
    $PLUGIN_HOOKS['add_css']['n3indicator']        = 'css/n3indicator.css';
    $PLUGIN_HOOKS['add_javascript']['n3indicator'] = 'js/n3indicator.js';

    $PLUGIN_HOOKS['config_page']['n3indicator'] = 'front/config.php';

    $PLUGIN_HOOKS[\Glpi\Plugin\Hooks::POST_SHOW_ITEM]['n3indicator'] =
        'plugin_n3indicator_post_show_item';
}

function plugin_n3indicator_post_show_item($params) {
    $item = $params['item'] ?? null;

    // Siempre inyecta el mapa de colores para el listado
    static $colorMapInjected = false;
    if (!$colorMapInjected) {
        $colorMapInjected = true;
        $groupNames = PluginN3indicatorTicket::getN3GroupNamesWithColors();
        echo '<script>window.N3_COLOR_MAP = ' . json_encode($groupNames) . ';</script>';
    }

    // Si es un ticket N3, agrega el marcador para el banner
    if ($item instanceof Ticket) {
        PluginN3indicatorTicket::showN3Banner(['item' => $item]);
    }
}

function plugin_version_n3indicator() {
    return [
        'name'         => 'N3 Indicator',
        'version'      => PLUGIN_N3INDICATOR_VERSION,
        'author'       => 'SSMSO - URCIT',
        'license'      => 'GPLv2+',
        'homepage'     => '',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_N3INDICATOR_MIN_GLPI,
                'max' => PLUGIN_N3INDICATOR_MAX_GLPI,
            ]
        ]
    ];
}

function plugin_n3indicator_check_prerequisites() {
    if (version_compare(GLPI_VERSION, PLUGIN_N3INDICATOR_MIN_GLPI, 'lt')
        || version_compare(GLPI_VERSION, PLUGIN_N3INDICATOR_MAX_GLPI, 'gt')) {
        echo 'Este plugin requiere GLPI entre '
            . PLUGIN_N3INDICATOR_MIN_GLPI . ' y ' . PLUGIN_N3INDICATOR_MAX_GLPI;
        return false;
    }
    return true;
}

function plugin_n3indicator_check_config() {
    return true;
}
