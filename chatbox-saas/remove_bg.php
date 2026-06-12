<?php
function removeWhiteBackground($src, $dest) {
    if (!file_exists($src)) {
        echo "File not found: $src\n";
        return false;
    }
    $imgData = file_get_contents($src);
    $img = imagecreatefromstring($imgData);
    if(!$img) {
        echo "Failed to load Image: $src\n";
        return false;
    }
    
    // Enable alpha blending
    imagealphablending($img, false);
    imagesavealpha($img, true);
    
    $width = imagesx($img);
    $height = imagesy($img);
    
    for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y++) {
            $color = imagecolorat($img, $x, $y);
            $r = ($color >> 16) & 0xFF;
            $g = ($color >> 8) & 0xFF;
            $b = $color & 0xFF;
            
            if ($r > 235 && $g > 235 && $b > 235) {
                $transparent = imagecolorallocatealpha($img, 255, 255, 255, 127);
                imagesetpixel($img, $x, $y, $transparent);
            }
        }
    }
    
    imagepng($img, $dest);
    imagedestroy($img);
    echo "Saved: $dest\n";
    return true;
}

if (!is_dir(__DIR__ . '/public/images/mascots')) {
    mkdir(__DIR__ . '/public/images/mascots', 0777, true);
}

$file3 = 'C:/Users/paiva/.gemini/antigravity-ide/brain/916fc45b-2dfe-4e56-aed9-065c95987eb0/mascot_concept_3_1781139982423.png';
$file4 = 'C:/Users/paiva/.gemini/antigravity-ide/brain/916fc45b-2dfe-4e56-aed9-065c95987eb0/mascot_concept_4_1781139990676.png';
$file5 = 'C:/Users/paiva/.gemini/antigravity-ide/brain/916fc45b-2dfe-4e56-aed9-065c95987eb0/mascot_concept_5_1781140000683.png';

removeWhiteBackground($file3, __DIR__ . '/public/images/mascots/robot.png');
removeWhiteBackground($file4, __DIR__ . '/public/images/mascots/man_2.png');
removeWhiteBackground($file5, __DIR__ . '/public/images/mascots/woman_2.png');
echo "Script finished.\n";
