<?php

namespace App\Services;

use App\Models\Experiment;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use SimpleXMLElement;

class ExperimentExportHandler
{
    protected $experiment;

    public function __construct(Experiment $experiment)
    {
        $this->experiment = $experiment;
    }

    public function handleExport(array $options)
    {
        $includeMedia = $options['include_media'] ?? false;
        $exportJson = $options['export_json'] ?? false;
        $exportXml = $options['export_xml'] ?? false;

        $filesToZip = [];

        if ($exportJson) {
            $jsonFilePath = $this->exportToJson();
            $filesToZip['Experiment.json'] = $jsonFilePath;
        }

        if ($exportXml) {
            $xmlFilePath = $this->exportToXml();
            $filesToZip['Experiment.xml'] = $xmlFilePath;
        }

        if ($includeMedia) {
            foreach ($this->experiment->media as $mediaPath) {
                $fullMediaPath = Storage::disk('public')->path($mediaPath);
                if (file_exists($fullMediaPath)) {
                    $filesToZip['media/' . basename($mediaPath)] = $fullMediaPath;
                }
            }
        }

        if (count($filesToZip) === 1) {
            $filePath = reset($filesToZip);
            return response()->download($filePath);
        } elseif (count($filesToZip) > 1) {
            $zipFilePath = $this->createZip($filesToZip);
            return response()->download($zipFilePath);
        } else {
            return response()->noContent();
        }
    }



    protected function prepareExportData()
    {
        return [
            'name' => $this->experiment->name,
            'description' => $this->experiment->description,
            'type' => $this->experiment->type,
            'media' => $this->experiment->media,
            'button_size' => $this->experiment->button_size,
            'button_color' => $this->experiment->button_color,
            'created_by' => $this->experiment->creator->name,
        ];
    }

    protected function exportToJson()
    {
        $data = $this->prepareExportData();
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $fileName = 'Experiment-' . $this->experiment->name . '-' . time() . '.json';
        $filePath = 'temp/' . $fileName;
        Storage::disk('public')->put($filePath, $jsonContent);

        return Storage::disk('public')->path($filePath);
    }

    protected function exportToXml()
    {
        $data = $this->prepareExportData();
        $xml = new SimpleXMLElement('<Experiment/>');
        $this->arrayToXml($data, $xml);
        $fileName = 'Experiment-' . $this->experiment->name . '-' . time() . '.xml';
        $filePath = 'temp/' . $fileName;
        Storage::disk('public')->put($filePath, $xml->asXML());

        return Storage::disk('public')->path($filePath);
    }

    protected function arrayToXml(array $data, SimpleXMLElement &$xml, $defaultNodeName = 'item')
    {
        foreach ($data as $key => $value) {
            $cleanKey = preg_replace('/[^a-z_]/i', '', $key);
            if (empty($cleanKey)) {
                $cleanKey = $defaultNodeName;
            }
            if (is_array($value)) {
                $subnode = $xml->addChild($cleanKey);
                $this->arrayToXml($value, $subnode);
            } else {
                $node = $xml->addChild($cleanKey);
                $node[0] = htmlspecialchars((string) $value);
            }
        }
    }

    public function downloadExperiment($experimentId, Request $request)
    {
        $experiment = Experiment::find($experimentId);
        if (!$experiment) {
            return response()->json(['error' => 'Experiment not found'], 404);
        }

        $handler = new ExperimentExportHandler($experiment);
        return $handler->handleExport($request->all());
    }

    protected function createZip(array $files)
    {
        $zip = new ZipArchive();
        $zipFileName = 'Experiment-' . $this->experiment->name . '-' . time() . '.zip';
        $zipFilePath = storage_path('app/public/temp/' . $zipFileName);

        if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
            foreach ($files as $name => $path) {
                if (file_exists($path)) {
                    $zip->addFile($path, $name);
                } else {
                    Log::error("File not found when adding to zip: $path");
                }
            }
            $zip->close();
            return $zipFilePath;
        } else {
            Log::error("Could not open zip file for writing: $zipFilePath");
            return null;
        }
    }
}
