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

    public function clipPolygons($geojson): ?array
    {
        $inputGeojson = $this->getTemporaryFile('input.geojson');
        $outputGeojson = $this->getTemporaryFile('output.geojson');

        $writeHandle = fopen($inputGeojson, 'w');

        try {
            fwrite($writeHandle, json_encode($geojson));
        } finally {
            fclose($writeHandle);
        }

        $process = new Process(['python3', base_path() . '/resources/python/polygon-clip/app.py', $inputGeojson, $outputGeojson]);

        $stdout = '';
        $stderr = '';

        $process->run(function ($type, $buffer) use (&$stdout, &$stderr) {
            if (Process::ERR === $type) {
                $stderr .= $buffer;
            } else {
                $stdout .= $buffer;
            }
        });

        if (! $process->isSuccessful()) {
            Log::error('Error running clip script: ' . $stderr);

            return null;
        }

        // Log warnings and errors, but don't include them in the result
        if (! empty($stderr)) {
            Log::warning('Python script warnings/errors: ' . $stderr);
        }

        // The actual result should be in the output file
        $result = json_decode(file_get_contents($outputGeojson), true);

        unlink($inputGeojson);
        unlink($outputGeojson);

        return $result;
    }

    public function IndicatorPolygon($geojson, $indicator_name, $api_key)
    {
        $inputGeojson = $this->getTemporaryFile('input.geojson');
        $outputGeojson = $this->getTemporaryFile('output.geojson');

        $writeHandle = fopen($inputGeojson, 'w');

        try {
            fwrite($writeHandle, json_encode($geojson));
        } finally {
            fclose($writeHandle);
        }

        $process = new Process(['python3', base_path() . '/resources/python/polygon-indicator/app.py', $inputGeojson, $outputGeojson, $indicator_name, $api_key]);

        $stdout = '';
        $stderr = '';

        $process->run(function ($type, $buffer) use (&$stdout, &$stderr) {
            if (Process::ERR === $type) {
                $stderr .= $buffer;
            } else {
                $stdout .= $buffer;
            }
        });

        if (! $process->isSuccessful()) {
            Log::error('Error running indicator script: ' . $stderr);

            return null;
        }

        if (! empty($stderr)) {
            Log::warning('Python script warnings/errors: ' . $stderr);
        }

        $result = json_decode(file_get_contents($outputGeojson), true);

        unlink($inputGeojson);
        unlink($outputGeojson);

        return $result;
    }

    protected function getTemporaryFile(string $prefix): string
    {
        return tempnam(sys_get_temp_dir(), $prefix);
    }
}
