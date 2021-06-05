<?php


namespace App\Service;


use App\Entity\User;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadService
{
    private Filesystem $fileSystem;
    private string $uploadPath;

    public function __construct(string $uploadPath, Filesystem $filesystem)
    {
        $this->fileSystem = $filesystem;
        $this->uploadPath = $uploadPath;
    }

    public function uploadAvatar(UploadedFile $avatarFile, User $user): array
    {
        $return = [
            'success' => false,
            'file' => ''
        ];
        $fileName = uniqid(rand(1000, 9999), true) . "." . $avatarFile->guessExtension();

        if (!$this->upload($avatarFile, '/avatars', $fileName)) {
            return $return;
        }
        $return['file'] = $fileName;

        if ($user->getAvatar()) {
            $this->deleteFile('/avatars/' . $user->getAvatar());
        }

        $this->resizeImage($this->uploadPath . '/avatars/' . $fileName, 256, 256);
        $return['success'] = true;
        return $return;
    }

    private function upload(UploadedFile $file, $directory, $fileName): bool
    {
        try {
            $file->move(
                $this->uploadPath . $directory,
                $fileName
            );
        } catch (FileException $e) {
            return false;
        }
        return true;
    }

    private function deleteFile($path)
    {
        $this->fileSystem->remove($this->uploadPath . $path);
    }

    private function resizeImage(string $imagePath, int $tWidth, int $tHeight)
    {
        $tRation = $tWidth / $tHeight;
        list($oWidth, $oHeight) = getimagesize($imagePath);
        $oRatio = $oWidth / $oHeight;

        $imagine = new Imagine();
        $image = $imagine->open($imagePath);

        if ($tRation > $oRatio) {
            $image->resize(new Box($tWidth, $tWidth / $oRatio));
            $image->crop(new Point(0, (($tWidth / $oRatio) - $tHeight) / 2), new Box($tWidth, $tHeight));
        } else {
            $image->resize(new Box($tHeight * $oRatio, $tHeight));
            $image->crop(new Point((($tHeight * $oRatio) - $tWidth) / 2,0), new Box($tWidth, $tHeight));
        }
        $image->save($imagePath);
    }
}