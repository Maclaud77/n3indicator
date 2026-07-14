<?php
class PluginN3indicatorTicket {

    const N3_PARENT_GROUP = 'URCIT SSMSO';
    const N3_GROUP_NAMES  = [
        'Infraestructura SSMSO',
        'Redes y Comunicaciones SSMSO',
        'Ciberseguridad SSMSO',
    ];

    private static ?array $cachedGroupIds = null;
    private static ?array $cachedColorMap = null;

    public static function getN3GroupIds(): array {
        global $DB;
        if (self::$cachedGroupIds !== null) return self::$cachedGroupIds;

        $rows = $DB->request(['FROM' => 'glpi_plugin_n3indicator_groups', 'WHERE' => ['is_active' => 1]]);
        $ids  = [];
        foreach ($rows as $row) $ids[] = (int) $row['groups_id'];

        if (!empty($ids)) { self::$cachedGroupIds = $ids; return $ids; }

        $parent   = $DB->request(['SELECT' => ['id'], 'FROM' => 'glpi_groups', 'WHERE' => ['name' => self::N3_PARENT_GROUP]]);
        $parentId = null;
        foreach ($parent as $p) { $parentId = (int) $p['id']; break; }

        if ($parentId) {
            $children = $DB->request(['SELECT' => ['id'], 'FROM' => 'glpi_groups', 'WHERE' => ['groups_id' => $parentId]]);
            foreach ($children as $c) $ids[] = (int) $c['id'];
        }

        if (empty($ids)) {
            $named = $DB->request(['SELECT' => ['id'], 'FROM' => 'glpi_groups', 'WHERE' => ['name' => self::N3_GROUP_NAMES]]);
            foreach ($named as $g) $ids[] = (int) $g['id'];
        }

        self::$cachedGroupIds = array_unique($ids);
        return self::$cachedGroupIds;
    }

    public static function getColorMap(): array {
        global $DB;
        if (self::$cachedColorMap !== null) return self::$cachedColorMap;

        $rows = $DB->request([
            'FROM'  => 'glpi_plugin_n3indicator_groups',
            'WHERE' => ['is_active' => 1],
        ]);

        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['groups_id']] = $row['color'] ?? '#d63031';
        }

        self::$cachedColorMap = $map;
        return $map;
    }

    public static function getN3CategoryIds(): array {
        global $DB;
        $rows = $DB->request(['FROM' => 'glpi_plugin_n3indicator_categories', 'WHERE' => ['is_active' => 1]]);
        $ids  = [];
        foreach ($rows as $row) $ids[] = (int) $row['itilcategories_id'];
        return $ids;
    }

    public static function isN3Ticket(int $tickets_id): bool {
        global $DB;
        if ($tickets_id <= 0) return false;

        $groupIds = self::getN3GroupIds();
        if (!empty($groupIds)) {
            $result = $DB->request([
                'FROM'  => 'glpi_groups_tickets',
                'WHERE' => ['tickets_id' => $tickets_id, 'type' => \CommonITILActor::ASSIGN, 'groups_id' => $groupIds],
                'LIMIT' => 1,
            ]);
            if (count($result) > 0) return true;
        }

        $catIds = self::getN3CategoryIds();
        if (!empty($catIds)) {
            $ticket = $DB->request(['SELECT' => ['itilcategories_id'], 'FROM' => 'glpi_tickets', 'WHERE' => ['id' => $tickets_id]]);
            foreach ($ticket as $t) {
                if (in_array((int)$t['itilcategories_id'], $catIds, true)) return true;
            }
        }

        return false;
    }

    public static function getTicketColor(int $tickets_id): string {
        global $DB;

        $colorMap = self::getColorMap();
        if (empty($colorMap)) return '#d63031';

        $groupIds = array_keys($colorMap);
        $result   = $DB->request([
            'FROM'  => 'glpi_groups_tickets',
            'WHERE' => ['tickets_id' => $tickets_id, 'type' => \CommonITILActor::ASSIGN, 'groups_id' => $groupIds],
            'LIMIT' => 1,
        ]);

        foreach ($result as $row) {
            return $colorMap[(int)$row['groups_id']] ?? '#d63031';
        }

        return '#d63031';
    }

    public static function showN3Banner(array $params): void {
        $item = $params['item'] ?? null;
        if (!($item instanceof \Ticket)) return;

        $id = (int) $item->getID();
        if ($id <= 0 || !self::isN3Ticket($id)) return;

        $color      = self::getTicketColor($id);
        $groupNames = self::getN3GroupNamesWithColors();

        echo '<script data-n3ticket="1" data-n3color="' . htmlspecialchars($color) . '"></script>';
        echo '<script>window.N3_COLOR_MAP = ' . json_encode($groupNames) . ';</script>';
    }

    public static function getN3GroupNamesWithColors(): array {
        global $DB;

        $rows = $DB->request([
            'SELECT'    => ['n.groups_id', 'n.color', 'g.name'],
            'FROM'      => 'glpi_plugin_n3indicator_groups AS n',
            'LEFT JOIN' => [
                'glpi_groups AS g' => ['FKEY' => ['n' => 'groups_id', 'g' => 'id']],
            ],
            'WHERE' => ['n.is_active' => 1],
        ]);

        $result = [];
        foreach ($rows as $row) {
            if (!empty($row['name'])) {
                $result[] = [
                    'keyword' => strtolower($row['name']),
                    'color'   => $row['color'] ?? '#d63031',
                ];
            }
        }

        if (empty($result)) {
            foreach (['infraestructura ssmso','redes y comunicaciones ssmso','ciberseguridad ssmso','urcit ssmso'] as $kw) {
                $result[] = ['keyword' => $kw, 'color' => '#d63031'];
            }
        }

        return $result;
    }
}
