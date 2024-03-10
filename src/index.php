<?php
// array to store <effect> elements
$effects = [];

foreach (glob('resource/*.swf') as $file) {
    // current file name itself
    $nameOld = trim(str_replace('.swf', '', basename($file)));

    // output it so user can choose new name
    echo "rename \"{$nameOld}\"\n";
    echo "> ";
    
    // read new name and define it
    $nameNew = trim(str_replace('.swf', '', fgets(STDIN)));
    
    // ask for old and new ID (u can find old ID in the swf itself, its fxWHATEVER)
    echo "old effect id?\n";
    echo "> ";
    $oldInput = trim(fgets(STDIN));
    echo "new effect id?\n";
    echo "> ";
    $newInput = trim(fgets(STDIN));

    // define old and new effect IDs based on user inputs
    $fxOld = "fx" . $oldInput;
    $fxNew = "fx" . $newInput;
    $fx2Old = "fx." . $oldInput;
    $fx2New = "fx." . $newInput;
    
    // turn old file to an editable temporary xml, which we can then rename to $nameNew
    shell_exec("java -jar ffdec/ffdec.jar -swf2xml $file resource/tmp_old.xml");
    
    // read them as hex, since they are flash (.swf)
    $hexOld = bin2hex($nameOld);
    $hexNew = bin2hex($nameNew);
    $hexFxOld = bin2hex($fxOld);
    $hexFxNew = bin2hex($fxNew);
    $hex2FxOld = bin2hex($fx2Old);
    $hex2FxNew = bin2hex($fx2New);
    
    // read the old XML and str_replace old name with new name in every hex target
    $xml = file_get_contents('resource/tmp_old.xml');
    $xml = str_replace([$nameOld, $hexOld], [$nameNew, $hexNew], $xml);
    $xml = str_replace([$fxOld, $hexFxOld], [$fxNew, $hexFxNew], $xml);
    $xml = str_replace([$fx2Old, $hex2FxOld], [$fx2New, $hex2FxNew], $xml);

    // Add new effect element to the effects array
    $newEffect = "<effect id=\"{$newInput}\" lib=\"{$nameNew}\" type=\"fx\" revision=\"69420\"/>";
    $effects[] = $newEffect;
    
    // create a new xml out of the old renamed
    file_put_contents('resource/tmp_new.xml', $xml);
    
    // run a console command to generate an swf out of the new xml
    shell_exec("java -jar ffdec/ffdec.jar -xml2swf resource/tmp_new.xml resource/renamed/{$nameNew}.swf");
    
    unlink('resource/tmp_new.xml');
    unlink('resource/tmp_old.xml');
}

// dump all <effect> elements into effectmap.xml
file_put_contents('resource/effectmap.xml', implode("\n", $effects));

exit('done');


