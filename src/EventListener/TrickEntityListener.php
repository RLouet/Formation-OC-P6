<?php

namespace App\EventListener;

use App\Entity\Image;
use App\Entity\Trick;
use App\Repository\TrickRepository;
use App\Service\UploadService;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

class TrickEntityListener
{
    private SluggerInterface $slugger;
    private TrickRepository $trickRepository;

    public function __construct(SluggerInterface $slugger, TrickRepository $trickRepository)
    {
        $this->slugger = $slugger;
        $this->trickRepository = $trickRepository;
    }

    public function prePersist(Trick $trick, LifecycleEventArgs $event): void
    {
        $trick->computeSlug($this->slugger, $this->trickRepository);
    }

    public function preUpdate(Trick $trick, LifecycleEventArgs $event): void
    {
        $trick->computeSlug($this->slugger, $this->trickRepository);
    }
}