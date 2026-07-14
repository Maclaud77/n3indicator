<?php
include('../../../inc/includes.php');

Session::checkRight('config', READ);

// Endpoint colormap para JS
if (isset($_GET['n3action']) && $_GET['n3action'] === 'colormap') {
    header('Content-Type: application/json');
    echo json_encode(PluginN3indicatorTicket::getN3GroupNamesWithColors());
    exit;
}

// Procesa acción GET directamente sin redirect
$action = $_GET['n3action'] ?? '';

if (!empty($action) && Session::haveRight('config', UPDATE)) {
    global $DB;

    switch ($action) {
        case 'add':
            $groupsId = (int)($_GET['groups_id'] ?? 0);
            $color    = preg_match('/^#[0-9a-fA-F]{6}$/i', $_GET['color'] ?? '') 
                        ? strtolower($_GET['color']) : '#d63031';
            if ($groupsId > 0) {
                $group = $DB->request(['SELECT' => ['name'], 'FROM' => 'glpi_groups', 'WHERE' => ['id' => $groupsId]]);
                $name  = '';
                foreach ($group as $g) $name = $g['name'];
                $exists = $DB->request(['FROM' => 'glpi_plugin_n3indicator_groups', 'WHERE' => ['groups_id' => $groupsId]]);
                if (count($exists) === 0) {
                    $DB->insert('glpi_plugin_n3indicator_groups', [
                        'groups_id' => $groupsId, 'name' => $name, 'color' => $color,
                        'is_active' => 1, 'date_mod' => date('Y-m-d H:i:s'),
                    ]);
                    $_SESSION['n3_msg'] = 'Grupo agregado correctamente.';
                    $_SESSION['n3_msg_type'] = 'success';
                } else {
                    $_SESSION['n3_msg'] = 'Este grupo ya está configurado.';
                    $_SESSION['n3_msg_type'] = 'warning';
                }
            }
            break;

        case 'update_color':
            $configId = (int)($_GET['config_id'] ?? 0);
            $color    = preg_match('/^#[0-9a-fA-F]{6}$/i', $_GET['color'] ?? '') 
                        ? strtolower($_GET['color']) : '#d63031';
            if ($configId > 0) {
                $DB->update('glpi_plugin_n3indicator_groups',
                    ['color' => $color, 'date_mod' => date('Y-m-d H:i:s')], ['id' => $configId]);
            }
            break;

        case 'toggle':
            $configId = (int)($_GET['config_id'] ?? 0);
            if ($configId > 0) {
                $row = $DB->request(['FROM' => 'glpi_plugin_n3indicator_groups', 'WHERE' => ['id' => $configId]]);
                foreach ($row as $r) {
                    $DB->update('glpi_plugin_n3indicator_groups',
                        ['is_active' => $r['is_active'] ? 0 : 1], ['id' => $configId]);
                }
            }
            break;

        case 'delete':
            $configId = (int)($_GET['config_id'] ?? 0);
            if ($configId > 0) {
                $DB->delete('glpi_plugin_n3indicator_groups', ['id' => $configId]);
                $_SESSION['n3_msg'] = 'Grupo eliminado.';
                $_SESSION['n3_msg_type'] = 'success';
            }
            break;
    }
}

Html::header(
    'N3 Indicator — Configuración',
    $_SERVER['PHP_SELF'],
    'config',
    'PluginN3indicatorConfig'
);

PluginN3indicatorConfig::showConfigPage();

Html::footer();
