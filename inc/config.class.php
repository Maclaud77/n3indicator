<?php
class PluginN3indicatorConfig {

    public static function showConfigPage(): void {
        global $DB;

        $configs = self::getAllConfigs();
        $groups  = self::getAvailableGroups();
        $base    = '/plugins/n3indicator/front/config.php';

        echo '<div class="container-fluid">';
        echo '<div class="card mt-3">';
        echo '<div class="card-header">';
        echo '<h3 class="card-title"><i class="fas fa-palette me-2"></i>Configuración de grupos N3 y colores</h3>';
        echo '</div>';
        echo '<div class="card-body">';

        // Mensajes flash
        if (isset($_SESSION['n3_msg'])) {
            $type = $_SESSION['n3_msg_type'] ?? 'success';
            echo '<div class="alert alert-' . $type . '">' . htmlspecialchars($_SESSION['n3_msg']) . '</div>';
            unset($_SESSION['n3_msg'], $_SESSION['n3_msg_type']);
        }

        // Formulario agregar via GET
        echo '<form method="GET" action="' . $base . '">';
        echo '<input type="hidden" name="n3action" value="add">';
        echo '<div class="row g-3 align-items-end mb-4">';

        echo '<div class="col-md-5">';
        echo '<label class="form-label fw-bold">Grupo N3</label>';
        echo '<select name="groups_id" class="form-select" required>';
        echo '<option value="">-- Seleccionar grupo --</option>';
        foreach ($groups as $id => $name) {
            echo '<option value="' . $id . '">' . htmlspecialchars($name) . '</option>';
        }
        echo '</select>';
        echo '</div>';

        echo '<div class="col-md-3">';
        echo '<label class="form-label fw-bold">Color de identificación</label>';
        echo '<div class="d-flex align-items-center gap-2">';
        echo '<input type="color" name="color" value="#d63031" class="form-control form-control-color" style="width:60px;height:38px;">';
        echo '<span class="text-muted small">Color del banner y fila</span>';
        echo '</div>';
        echo '</div>';

        echo '<div class="col-md-2">';
        echo '<button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus me-1"></i>Agregar</button>';
        echo '</div>';
        echo '</div>';
        echo '</form>';

        // Tabla
        if (!empty($configs)) {
            echo '<hr><h5 class="mb-3">Grupos configurados</h5>';
            echo '<table class="table table-hover">';
            echo '<thead class="table-light"><tr><th>Grupo</th><th>Color</th><th>Vista previa</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>';

            foreach ($configs as $config) {
                $color     = htmlspecialchars($config['color'] ?? '#d63031');
                $groupName = htmlspecialchars($config['group_name'] ?? 'Grupo desconocido');
                $id        = (int) $config['id'];

                echo '<tr>';
                echo '<td><strong>' . $groupName . '</strong></td>';

                // Color con JS para actualizar via GET
                echo '<td><div class="d-flex align-items-center gap-2">';
                echo '<input type="color" value="' . $color . '" class="form-control form-control-color" style="width:50px;height:32px;" ';
                echo 'onchange="window.location=\'' . $base . '?n3action=update_color&config_id=' . $id . '&color=\'+encodeURIComponent(this.value)">';
                echo '<code>' . $color . '</code>';
                echo '</div></td>';

                echo '<td><span style="display:inline-block;background:' . $color . '20;border-left:4px solid ' . $color . ';padding:4px 12px;border-radius:4px;font-size:0.85rem;">';
                echo '<strong style="color:' . $color . '">N3</strong> ' . $groupName . '</span></td>';

                $activeClass = $config['is_active'] ? 'success' : 'secondary';
                $activeLabel = $config['is_active'] ? 'Activo' : 'Inactivo';
                echo '<td><span class="badge bg-' . $activeClass . '">' . $activeLabel . '</span></td>';

                echo '<td>';
                $toggleLabel = $config['is_active'] ? 'Desactivar' : 'Activar';
                $toggleClass = $config['is_active'] ? 'btn-warning' : 'btn-success';
                echo '<a href="' . $base . '?n3action=toggle&config_id=' . $id . '" class="btn btn-sm ' . $toggleClass . ' me-1">' . $toggleLabel . '</a>';
                echo '<a href="' . $base . '?n3action=delete&config_id=' . $id . '" class="btn btn-sm btn-danger" onclick="return confirm(\'¿Eliminar?\')">';
                echo '<i class="fas fa-trash"></i></a>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<div class="alert alert-info mt-3"><i class="fas fa-info-circle me-2"></i>No hay grupos configurados.</div>';
        }

        echo '</div></div></div>';
    }

    public static function getAllConfigs(): array {
        global $DB;
        $rows = $DB->request([
            'SELECT'    => ['n.id', 'n.groups_id', 'n.name', 'n.color', 'n.is_active', 'g.name AS group_name'],
            'FROM'      => 'glpi_plugin_n3indicator_groups AS n',
            'LEFT JOIN' => ['glpi_groups AS g' => ['FKEY' => ['n' => 'groups_id', 'g' => 'id']]],
            'ORDERBY'   => 'g.name ASC',
        ]);
        $result = [];
        foreach ($rows as $row) $result[] = $row;
        return $result;
    }

    public static function getAvailableGroups(): array {
        global $DB;
        $rows = $DB->request(['SELECT' => ['id', 'name'], 'FROM' => 'glpi_groups', 'ORDERBY' => 'name ASC']);
        $groups = [];
        foreach ($rows as $row) $groups[(int)$row['id']] = $row['name'];
        return $groups;
    }
}
