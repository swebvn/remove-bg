<?php

namespace Swebvn\RemoveBg;

use Codewithkyrian\Transformers\Exceptions\HubException;
use Codewithkyrian\Transformers\Exceptions\UnsupportedModelTypeException;
use Codewithkyrian\Transformers\Models\Auto\AutoModel;
use Codewithkyrian\Transformers\Processors\AutoProcessor;
use Codewithkyrian\Transformers\Transformers;
use Codewithkyrian\Transformers\Utils\Image;
use Codewithkyrian\Transformers\Utils\ImageDriver;

class RemoveBackground
{
    public function __construct(protected string $modelName = 'briaai/RMBG-1.4')
    {
        Transformers::setup()->setImageDriver(ImageDriver::GD);
    }

    /**
     * @throws HubException
     * @throws UnsupportedModelTypeException
     */
    public function handle(string $filePath): string
    {
        $model = AutoModel::fromPretrained($this->modelName);
        $processor = AutoProcessor::fromPretrained($this->modelName);

        $image = Image::read($filePath);
        ['pixel_values' => $pixelValues] = $processor($image);
        ['output' => $output] = $model(['input'=>  $pixelValues]);

        $mask = Image::fromTensor($output[0]->multiply(255))
            ->resize($image->width(), $image->height());

        $maskedImage = $image->applyMask($mask);

        return $maskedImage->image->get('png');
    }
}