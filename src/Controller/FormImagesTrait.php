<?php


namespace App\Controller;


use App\Entity\Image;
use App\Service\UploadService;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Form\FormInterface;

trait FormImagesTrait
{
    private function handleHero(?string $heroField): bool|array
    {
        $hero = false;
        preg_match_all("/^(?<type>new|old)-(?<index>\d{1,4})$/i", $heroField, $matches);
        if (!empty($matches[0])) {
            $hero = [
                'all' => (string)$matches[0][0],
                'type' => (string)$matches['type'][0],
                'index' => (int)$matches['index'][0]
            ];
        }
        return $hero;
    }

    private function processNewImages (FormInterface $form, UploadService $uploadService): bool
    {
        $hero = $this->handleHero($form->get('hero')->getData());
        $success = true;
        foreach ($form['newImages'] as $key => $imageForm) {
            $imageFile = $uploadService->getFormFile($form->get('newImages')[$key], 'name');
            if ($imageFile) {
                $upload = $uploadService->uploadTrickImage($imageFile);
                if (!$upload['success']) {
                    $success = false;
                }
                if ($upload['success']) {
                    $trick = $form->getData();
                    $image = new Image();
                    $image->setName($upload['file']);
                    $trick->addImage($image);
                    if ($hero && $hero['type'] === "new" && $hero['index'] === $key) {
                        $trick->setHero($image);
                    }
                }
            }
        }

        return $success;
    }

    private function processOldImages (FormInterface $form, UploadService $uploadService): bool
    {
        $hero = $this->handleHero($form->get('hero')->getData());
        $success = true;
        foreach ($form['images'] as $key => $imageForm) {
            $imageFile = $uploadService->getFormFile($form->get('images')[$key], 'newFile');
            $image = $imageForm->getData();
            if ($imageFile) {
                $upload = $uploadService->uploadTrickImage($imageFile);
                if (!$upload['success']) {
                    $success = false;
                }
                if ($upload['success']) {
                    $uploadService->deleteFile('/tricks/' . $image->getName());
                    $image->setName($upload['file']);
                }
            }
            if ($hero && $hero['type'] === "old" && $hero['index'] === $key) {
                $form->getData()->setHero($image);
            }
        }

        return $success;
    }
}