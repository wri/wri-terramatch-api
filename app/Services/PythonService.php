<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * A service for interfacing with external python scripts.
 */
class PythonService
{
    public function voronoiTransformation($geojson): ?array
    {
        $inputGeojson = $this->getTemporaryFile('input.geojson');
        $outputGeojson = $this->getTemporaryFile('output.geojson');

        $writeHandle = fopen($inputGeojson, 'w');

        try {
            fwrite($writeHandle, json_encode($geojson));
        } finally {
            fclose($writeHandle);
        }

        $process = new Process(['python3', base_path() . '/resources/python/polygon-voronoi/app.py', $inputGeojson, $outputGeojson]);
        $process->run();
        if (! $process->isSuccessful()) {
            Log::error('Error running voronoi script: ' . $process->getErrorOutput());

            return null;
        }

        $result = json_decode(file_get_contents($outputGeojson), true);

        unlink($inputGeojson);
        unlink($outputGeojson);

        return $result;
    }

    // public function clipPolygons($geojson): ?array
    // {
    //     $inputGeojson = $this->getTemporaryFile('input.geojson');
    //     $outputGeojson = $this->getTemporaryFile('output.geojson');

    //     $writeHandle = fopen($inputGeojson, 'w');

    //     try {
    //         fwrite($writeHandle, json_encode($geojson));
    //     } finally {
    //         fclose($writeHandle);
    //     }

    //     $process = new Process(['python3', base_path() . '/resources/python/polygon-clip/app.py', $inputGeojson, $outputGeojson]);
    //     $process->run(function ($type, $buffer) {
    //         echo $buffer;
    //     });
    //     // $process->run();
    //     if (! $process->isSuccessful()) {
    //         Log::error('Error running clip script: ' . $process->getErrorOutput());

    //         return null;
    //     }

    //     $result = json_decode(file_get_contents($outputGeojson), true);
    //     unlink($inputGeojson);
    //     unlink($outputGeojson);

    //     return $result;
    // }

    public function clipPolygons($geojson)
    {
        $tempInputFile = tempnam(sys_get_temp_dir(), 'input_geojson_');
        file_put_contents($tempInputFile, json_encode($geojson));

        $command = "python3 /path/to/app.py {$tempInputFile} /dev/null 2>&1";

        $process = proc_open($command, [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ], $pipes);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        unlink($tempInputFile);

        // Log the stderr content (Python log messages)
        if (!empty($stderr)) {
            Log::warning("Python script warnings/errors: " . $stderr);
        }

        // Return only the stdout content (the actual JSON output)
        return json_decode($stdout, true);
    }

    protected function getTemporaryFile(string $prefix): string
    {
        return tempnam(sys_get_temp_dir(), $prefix);
    }
}
