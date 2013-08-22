#!/usr/bin/env php
<?php
include __DIR__ . "/vendor/autoload.php";
include __DIR__ . "/src/bootstrap.php";

/*
 * Function must return array of model classes
 */
function lookup_models() {
    global $app;
    $models = [];
    foreach ($app->config("comps") as $path) {
        $dir = opendir(realpath(""
            . $path 
            . DIRECTORY_SEPARATOR 
            . "Model"
        ));
        if ($dir !== false) {
            while (false !== ($file = readdir($dir))) {
                if (array_search($file, [".", ".."]) === false) {
                    $info = pathinfo(realpath(""
                        . $path
                        . DIRECTORY_SEPARATOR
                        . "Model"
                        . DIRECTORY_SEPARATOR
                        . $file
                    ));

                    if ($info["extension"] == "php") {
                        $models[] = "\\"
                            . str_replace(DIRECTORY_SEPARATOR, "\\", $path)
                            . "\\"
                            . "Model\\"
                            . $info["filename"];
                    }
                }
            }
        }
    }

    return $models;
}

use \AutoParis\Generator;

function parse_arguments($argv) {
    array_shift($argv);
    $out = array();
    foreach ($argv as $arg) {
        if (substr($arg, 0, 2) == '--') {
            $eqPos = strpos($arg, '=');
            if ($eqPos === false) {
                $key = substr($arg, 2);
                $out[$key] = isset($out[$key]) ? $out[$key] : true;
            } else {
                $key = substr($arg, 2, $eqPos - 2);
                $out[$key] = substr($arg, $eqPos + 1);
            }
        }
        else if (substr($arg, 0, 1) == '-') {
            if (substr($arg, 2, 1) == '=') {
                $key = substr($arg, 1, 1);
                $out[$key] = substr($arg, 3);
            } else {
                $chars = str_split(substr($arg, 1));
                foreach ($chars as $char) {
                    $key = $char;
                    $out[$key] = isset($out[$key]) ? $out[$key] : true;
                }
            }
        } else {
            $out[] = $arg;
        }
    }
    return $out;
}

function update_model($model, $orm, $opts) {
    if (!(new $model() instanceof \AutoParis\AutoModel)) {
        return;
    }
    echo sprintf("Processing %s..." . PHP_EOL, $model);

    $result = Generator::prepare($model, $orm);
    $status = $result[0];
    $sql = $result[1];
    $fields = $result[2];

    if ($status == 0)
        echo sprintf(
            "Added - %d, deleted - %d, modified - %d, unmodified - %d." . PHP_EOL,
            count($fields["added"]),
            count($fields["deleted"]),
            count($fields["modified"]),
            count($fields["unmodified"])
        );

    if (count($sql) == 0) {
        echo "Nothing to do." . PHP_EOL;
        return;
    }

    if ($opts["sql"] === true && count($sql)) {
        echo implode(PHP_EOL, $sql) . PHP_EOL;
    } else {
        if (in_array($status, [0, Generator::$NEW_TABLE]) || $opts["force"] === true) {
            foreach ($sql as $line) {
                $orm::get_db()->exec($line);
            }
            echo "Up to date." . PHP_EOL;
        } else {
            switch ($status) {
            case Generator::$FIELD_MOD:
                echo "One of fields modified. Use --force if you really want to modify it." . PHP_EOL;
                break;
            }
        }
    }
}

$opts = parse_arguments($argv);

if (array_key_exists("help", $opts)) {
    echo "AutoParis scheme generator, version 0.1" . PHP_EOL . PHP_EOL;
    echo "--sql - display only sql" . PHP_EOL;
    echo "--model=[MODEL] - process only MODEL" . PHP_EOL;
    echo "--force - force update" . PHP_EOL;
    echo PHP_EOL . "http://shadowprince.github.com/autoparis" . PHP_EOL;
} else if (array_key_exists("model", $opts)) {
    update_model($app->getCC($opts["model"]), ORM, $opts);
} else {
    foreach (lookup_models() as $model) {
        update_model($model, ORM, $opts);
        echo PHP_EOL;
    }
}
