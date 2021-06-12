<?php

namespace App\EventListener;

use App\Entity\Image;
use App\Service\UploadService;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class ImageDeleteListener
{
    private UploadService $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function preRemove(Image $image, LifecycleEventArgs $event): void
    {
        $this->uploadService->deleteFile('/tricks/' . $image->getName());
    }


}