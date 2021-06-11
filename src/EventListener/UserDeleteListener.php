<?php

namespace App\EventListener;

use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\User;
use App\Service\UploadService;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class UserDeleteListener
{
    private UploadService $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function preRemove(User $user, LifecycleEventArgs $event): void
    {
        if ($user->getAvatar()) {
            $this->uploadService->deleteFile('/avatars/' . $user->getAvatar());
        }
    }


}