<?php
namespace Autoparis;

class Generator {
    public static $FIELD_MOD = 1;
    public static $NEW_TABLE = 2;


    protected static function generateFieldSql($field) {
        return sprintf("`%s` %s", $field->getName(), $field->getType(1));
    }

    protected static function generateTableSql($table, $fields) {
        $sfields = [];
        foreach ($fields as $field) {
            $sfields[] = self::generateFieldSql($field); 
        }

        $sql = array(
            sprintf("CREATE TABLE IF NOT EXISTS %s", $table),
            "(",
            implode(", ", $sfields),
            ");",
        );

        return implode(" ", $sql);
    }
    protected static function getFreshName($name) {
        return $name . "_fresh";
    }

    protected static function createFreshTable($model, $orm) {
        $sql = self::generateTableSql(
            self::getFreshName($model::$_table),
            (new $model())->getFields()
        );
        $orm::get_db()->exec($sql);
    }

    protected static function dropFreshTable($model, $orm) {
        $orm::get_db()->exec(sprintf(
            "DROP TABLE %s", self::getFreshName($model::$_table)
        ));
    }

    protected static function getModelField($model, $key) {
        foreach ((new $model())->getFields() as $field) {
            if ($field->getName() == $key)
                return $field;
        }

        return null;
    }

    public static function getTableSnapshot($table, $orm) {
        try {
            $shot = $orm::for_table($table)->raw_query("SHOW COLUMNS FROM " . $table)->find_array();
        } catch (\PDOException $e) {
            return [];
        }
        $res = [];
        foreach ($shot as $field) {
            $res[$field['Field']] = $field;
        }

        return $res;
    }

    /**
     * @TODO: rename $old
     */
    public static function getDiff($fresh, $old) {
        $fields = [
            "added" => [],
            "deleted" => [],
            "modified" => [],
            "unmodified" => [],
        ];

        foreach ($fresh as $name => $field) {
            if (array_key_exists($name, $old)) {
                if ($field == $old[$name])
                    $fields["unmodified"][] = $field;
                else
                    $fields["modified"][] = $field;
            } else {
                $fields["added"][] = $field;
            }
            unset($old[$name]);
        }
        $fields["deleted"] = $old;

        return $fields;
    }

    public static function prepare($model, $orm) {
        $oldss = self::getTableSnapshot($model::$_table, $orm);
        if (count($oldss) == 0)
            return self::create($model, $orm);
        else
            return self::update($model, $orm);
    }

    protected static function create($model, $orm) {
        $sql = self::generateTableSql(
            $model::$_table,
            (new $model())->getFields()
        );

        return [
            self::$NEW_TABLE,
            [$sql],
        ];
    }

    protected static function update($model, $orm) {
        self::createFreshTable($model, $orm);

        $fields = self::getDiff(
            self::getTableSnapshot(self::getFreshName($model::$_table), $orm),
            self::getTableSnapshot($model::$_table, $orm)
        );

        $sql = [];
        $status = 0;

        foreach ($fields["added"] as $field) {
            $sql[] = sprintf(
                "ALTER TABLE %s ADD %s", 
                $model::$_table, 
                self::generateFieldSql(self::getModelField($model, $field["Field"]))
            );
        }

        foreach ($fields["deleted"] as $field) {
            $sql[] = sprintf(
                "ALTER TABLE %s DROP COLUMN `%s`",
                $model::$_table,
                $field["Field"]
            );
        }

        foreach ($fields["modified"] as $field) {
            $sql[] = sprintf(
                "ALTER TABLE %s MODIFY %s",
                $model::$_table,
                self::generateFieldSql(self::getModelField($model, $field["Field"]))
            );
            $status = self::$FIELD_MOD;
        }

        self::dropFreshTable($model, $orm);
        return [
            $status,
            $sql,
            $fields,
        ];
    }
}
