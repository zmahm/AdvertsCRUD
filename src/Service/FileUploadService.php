<?php

namespace App\Service;

use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploadService
{
    private SluggerInterface $slugger;
    private string $uploadDirectory;

    public function __construct(SluggerInterface $slugger, string $uploadDirectory)
    {
        $this->slugger = $slugger;
        $this->uploadDirectory = $uploadDirectory;
    }

    // Uploads an image and returns the generated filename.
    public function uploadImage($image): string
    {
        $filename = $this->slugger->slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME))
            . '-' . uniqid() . '.' . $image->guessExtension();

        $image->move($this->uploadDirectory, $filename);

        return $filename;
    }

    // Deletes a single image file from the filesystem.
    public function deleteImage(string $filename): void
    {
        $filepath = $this->uploadDirectory . '/' . $filename;

        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    // Deletes multiple image files from the filesystem.
    public function deleteImages(array $filenames): void
    {
        foreach ($filenames as $filename) {
            $this->deleteImage($filename);
        }
    }
}
