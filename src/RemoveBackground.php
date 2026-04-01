<?php

namespace Swebvn\RemoveBg;

use Codewithkyrian\Transformers\Exceptions\HubException;
use Codewithkyrian\Transformers\Exceptions\UnsupportedModelTypeException;
use Codewithkyrian\Transformers\Models\Auto\AutoModel;
use Codewithkyrian\Transformers\Processors\AutoProcessor;
use Codewithkyrian\Transformers\Transformers;
use Codewithkyrian\Transformers\Utils\Image;
use Codewithkyrian\Transformers\Utils\ImageDriver;
use RuntimeException;

class RemoveBackground
{
    public function __construct(protected string $modelName = 'briaai/RMBG-1.4')
    {
    }

    /**
     * Remove background using the specified driver.
     *
     * @param string $driver 'rembg' or 'transformers'
     * @throws RuntimeException
     */
    public function handle(string $filePath, string $driver = 'rembg'): string
    {
        return match ($driver) {
            'transformers' => $this->handleWithTransformers($filePath),
            default => $this->handleWithRembg($filePath),
        };
    }

    /**
     * Remove background using rembg Python CLI tool.
     * Requires: pip install rembg[cli]
     *
     * @throws RuntimeException
     */
    public function handleWithRembg(string $filePath): string
    {
        $outputPath = sys_get_temp_dir() . '/' . uniqid('rembg_') . '.png';

        $command = sprintf(
            'rembg i %s %s 2>&1',
            escapeshellarg($filePath),
            escapeshellarg($outputPath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new RuntimeException('rembg failed: ' . implode("\n", $output));
        }

        if (!file_exists($outputPath)) {
            throw new RuntimeException('rembg did not produce output file');
        }

        $content = file_get_contents($outputPath);
        @unlink($outputPath);

        return $content;
    }

    /**
     * Remove background using PHP Transformers (old method).
     *
     * @throws HubException
     * @throws UnsupportedModelTypeException
     */
    public function handleWithTransformers(string $filePath): string
    {
        Transformers::setup()->setImageDriver(ImageDriver::GD);

        $model = AutoModel::fromPretrained($this->modelName);
        $processor = AutoProcessor::fromPretrained($this->modelName);

        $image = Image::read($filePath);
        ['pixel_values' => $pixelValues] = $processor($image);
        ['output' => $output] = $model(['input' => $pixelValues]);

        $mask = Image::fromTensor($output[0]->multiply(255))
            ->resize($image->width(), $image->height());

        $maskedImage = $image->applyMask($mask);

        return $maskedImage->image->get('png');
    }
}