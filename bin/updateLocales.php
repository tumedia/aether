#!/usr/bin/php
<?php
/**
 * Util script for both creating the locale structure, finding translations
 * and compiling the .mo-files for the translations used by php.
 *
 * Run with locale names as args from your project root.
 * ex. ~/aether/bin/updateLocales.php nb_NO sv_SE da_DK
 */
$path = getcwd();

if (count($argv) < 2) {
    echo "Will search all subfolders for phpfiles and save translations strings\n";
    echo "Run as {$argv[0]} <lang1> [<langN> ...]\n";
    echo "Ex. {$argv[0]} nn_NO sv_SE da_DK\n";
    exit(-1);
}

$pathBits = explode("/", $path);
while (true) {
    if (count($pathBits) === 0) {
        echo "Missing config and template dir\n";
        echo "Run script from project root\n";
        exit(-1);
    }
    $testPath = join("/", $pathBits);
    // Test if this seems to be a aether project root and break out if it is
    if (is_dir($testPath . "/config") && is_dir($testPath . "/templates")) {
        $path = $testPath;
        break;
    }
    array_pop($pathBits);
}

$locales = array_slice($argv, 1);

$localeDir = $path . "/locale";
$templateFile = $localeDir . "/messages.pot";

// use xgettext to fetch translation strings and dump to stdout
$h = popen("find . -name '*.php' -exec xgettext --no-location -o - \"{}\" \\;", "r");
$localeData = stream_get_contents($h);

// header needs to have charset set or it will fail to read translations
$localeData = str_replace("Content-Type: text/plain; charset=CHARSET", "Content-Type: text/plain; charset=UTF-8", $localeData);

file_put_contents($templateFile, $localeData);
echo "Generated {$templateFile}\n";

foreach ($locales as $l) {
    $localeDir = $path . "/locale/" . $l . "/LC_MESSAGES";
    $localeFile = $localeDir . "/messages.po";
    $localeFileMo = $localeDir . "/messages.mo";
    @mkdir($localeDir, 0755, true);

    // Merge new keys from template to locale files
    if (file_exists($localeFile)) {
        exec("msgmerge -U --backup=off \"{$localeFile}\" \"{$templateFile}\"");
        exec("msgfmt -o \"{$localeFileMo}\" \"{$localeFile}\"");
        echo "Updated file {$localeFile}\n";
        echo "Generated system file {$localeFileMo}\n";
    }
    else {
        copy($templateFile, $localeFile);
        echo "Created new translation file {$localeFile}\n";
    }
}

