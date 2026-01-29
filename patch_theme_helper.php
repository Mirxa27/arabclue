<?php
$file = 'app/Helpers/Classes/ThemeHelper.php';
$content = file_get_contents($file);
$content = str_replace(
    '$theme_google_fonts = array_merge(LiveCustomizer::getFontSetting(), $theme_google_fonts);',
    '$theme_google_fonts = array_merge(LiveCustomizer::getFontSetting(), $theme_google_fonts ?? []);',
    $content
);
file_put_contents($file, $content);
echo "Patched ThemeHelper.php\n";
