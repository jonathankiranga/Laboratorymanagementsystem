<?php
// Example DAQ data
$daq_data = [
    ['Timestamp' => '2024-11-14 12:00:00', 'Measurement' => 1.5],
    ['Timestamp' => '2024-11-14 12:05:00', 'Measurement' => 1.8],
    ['Timestamp' => '2024-11-14 12:10:00', 'Measurement' => 2.0],
    ['Timestamp' => '2024-11-14 12:15:00', 'Measurement' => 2.2],
];

// Set image dimensions
$width = 800;
$height = 400;
$image = imagecreate($width, $height);

// Set colors
$bg_color = imagecolorallocate($image, 255, 255, 255);  // White background
$line_color = imagecolorallocate($image, 0, 0, 255);  // Blue line color
$axis_color = imagecolorallocate($image, 0, 0, 0);  // Black for axes
$font_color = imagecolorallocate($image, 0, 0, 0); // Black for text
$font = 5;  // Built-in GD font

// Draw axes
imageline($image, 50, 50, 50, $height - 50, $axis_color);  // Y axis
imageline($image, 50, $height - 50, $width - 50, $height - 50, $axis_color);  // X axis

// Prepare the data for plotting
$max_measurement = max(array_column($daq_data, 'Measurement'));
$scale_x = ($width - 100) / count($daq_data); // Space between each data point on X axis
$scale_y = ($height - 100) / $max_measurement; // Scale for Y axis

// Plot the data points
for ($i = 0; $i < count($daq_data) - 1; $i++) {
    $x1 = 50 + $i * $scale_x;
    $y1 = $height - 50 - $daq_data[$i]['Measurement'] * $scale_y;
    $x2 = 50 + ($i + 1) * $scale_x;
    $y2 = $height - 50 - $daq_data[$i + 1]['Measurement'] * $scale_y;

    imageline($image, $x1, $y1, $x2, $y2, $line_color);
}

// Add X-axis label (e.g., "Timestamp")
imagestring($image, $font, $width / 2 - 50, $height - 30, 'Timestamp', $font_color);

// Add Y-axis label (e.g., "Measurement")
imagestringup($image, $font, 10, $height / 2, 'Measurement', $font_color);
for ($i = 0; $i < count($daq_data); $i++) {
    $x = 50 + $i * $scale_x;
    
    // Draw tick mark
    imageline($image, $x, $height - 50, $x, $height - 40, $axis_color);  // Tick mark
    
    // Label the X-axis (show Timestamp)
    imagestring($image, $font, $x - 20, $height - 25, $daq_data[$i]['Timestamp'], $font_color);
}

$num_ticks = 5; // Number of tick marks on the Y-axis
$step_y = $max_measurement / $num_ticks; // The interval between ticks
for ($i = 0; $i <= $num_ticks; $i++) {
    $y = $height - 50 - ($i * ($height - 100) / $num_ticks);
    
    // Draw tick mark
    imageline($image, 45, $y, 55, $y, $axis_color);  // Tick mark
    
    // Label the Y-axis (show Measurement value)
    imagestring($image, $font, 10, $y - 5, round($i * $step_y, 2), $font_color);
}
// Output the image as PNG
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>
