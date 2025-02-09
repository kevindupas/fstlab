<?php

namespace App\Services;

use App\Models\Experiment;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;
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
        $exportYaml = $options['export_yaml'] ?? false;

        $filesToZip = [];

        // Export des formats de données
        if ($exportJson) {
            $jsonFilePath = $this->exportToJson();
            $filesToZip['data/experiment.json'] = $jsonFilePath;
        }

        if ($exportXml) {
            $xmlFilePath = $this->exportToXml();
            $filesToZip['data/experiment.xml'] = $xmlFilePath;
        }

        if ($exportYaml) {
            $yamlFilePath = $this->exportToYaml();
            $filesToZip['data/experiment.yaml'] = $yamlFilePath;
        }

        // Ajout des fichiers Markdown
        $descriptionPath = $this->createMarkdownFile('description.md', $this->experiment->description);
        $instructionPath = $this->createMarkdownFile('instruction.md', $this->experiment->instruction);
        $filesToZip['docs/description.md'] = $descriptionPath;
        $filesToZip['docs/instruction.md'] = $instructionPath;

        // Ajout du fichier DOI
        $doiPath = $this->createDoiFile();
        $filesToZip['doi.txt'] = $doiPath;

        // Ajout des documents
        if ($this->experiment->documents) {
            foreach ($this->experiment->documents as $documentPath) {
                $fullDocPath = Storage::disk('public')->path($documentPath);
                if (file_exists($fullDocPath)) {
                    $filesToZip['documents/' . basename($documentPath)] = $fullDocPath;
                }
            }
        }

        // Ajout des médias si demandé
        if ($includeMedia && $this->experiment->media) {
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
        }

        return response()->noContent();
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
            'instruction' => $this->experiment->instruction,
            'documents' => $this->experiment->documents,
            'doi' => $this->experiment->doi,
        ];
    }

    protected function exportToJson()
    {
        $data = $this->prepareExportData();
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $fileName = 'experiment-' . time() . '.json';
        $filePath = 'temp/' . $fileName;
        Storage::disk('public')->put($filePath, $jsonContent);

        return Storage::disk('public')->path($filePath);
    }

    protected function exportToXml()
    {
        $data = $this->prepareExportData();
        $xml = new SimpleXMLElement('<Experiment/>');
        $this->arrayToXml($data, $xml);
        $fileName = 'experiment-' . time() . '.xml';
        $filePath = 'temp/' . $fileName;
        Storage::disk('public')->put($filePath, $xml->asXML());

        return Storage::disk('public')->path($filePath);
    }

    protected function exportToYaml()
    {
        $data = $this->prepareExportData();
        $yamlContent = Yaml::dump($data, 4, 2);
        $fileName = 'experiment-' . time() . '.yaml';
        $filePath = 'temp/' . $fileName;
        Storage::disk('public')->put($filePath, $yamlContent);
        return Storage::disk('public')->path($filePath);
    }

    protected function createMarkdownFile($filename, $content)
    {
        $filePath = 'temp/' . time() . '-' . $filename;
        Storage::disk('public')->put($filePath, $content);
        return Storage::disk('public')->path($filePath);
    }

    protected function createDoiFile()
    {
        $filePath = 'temp/' . time() . '-doi.txt';
        $content = $this->experiment->doi ?? 'No DOI available';
        Storage::disk('public')->put($filePath, $content);
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

    protected function createZip(array $files)
    {
        $zip = new ZipArchive();
        $zipFileName = 'experiment-' . time() . '.zip';
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
        }

        Log::error("Could not open zip file for writing: $zipFilePath");
        return null;
    }
}
