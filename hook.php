<?php
function plugin_n3indicator_install() {
    global $DB;

    $migration = new Migration(PLUGIN_N3INDICATOR_VERSION);

    if (!$DB->tableExists('glpi_plugin_n3indicator_groups')) {
        $query = "CREATE TABLE `glpi_plugin_n3indicator_groups` (
            `id`        INT unsigned NOT NULL AUTO_INCREMENT,
            `groups_id` INT NOT NULL DEFAULT 0,
            `name`      VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
            `color`     VARCHAR(7)   COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#d63031',
            `is_active` TINYINT NOT NULL DEFAULT 1,
            `date_mod`  DATETIME DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `groups_id` (`groups_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $migration->addPreQuery($query);
    } else {
        if (!$DB->fieldExists('glpi_plugin_n3indicator_groups', 'color')) {
            $migration->addField('glpi_plugin_n3indicator_groups', 'color', 'string', ['value' => '#d63031', 'after' => 'name']);
        }
    }

    if (!$DB->tableExists('glpi_plugin_n3indicator_categories')) {
        $query = "CREATE TABLE `glpi_plugin_n3indicator_categories` (
            `id`                INT unsigned NOT NULL AUTO_INCREMENT,
            `itilcategories_id` INT NOT NULL DEFAULT 0,
            `name`              VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
            `is_active`         TINYINT NOT NULL DEFAULT 1,
            `date_mod`          DATETIME DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `itilcategories_id` (`itilcategories_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $migration->addPreQuery($query);
    }

    $migration->executeMigration();
    return true;
}

function plugin_n3indicator_uninstall() {
    global $DB;

    $migration = new Migration(PLUGIN_N3INDICATOR_VERSION);

    foreach (['glpi_plugin_n3indicator_groups', 'glpi_plugin_n3indicator_categories'] as $table) {
        if ($DB->tableExists($table)) {
            $migration->dropTable($table);
        }
    }

    $migration->executeMigration();
    return true;
}
