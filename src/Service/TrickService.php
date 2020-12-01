<?php

namespace App\Service;

class TrickService
{
    public function cleanUpTitle($titleToClean)
    {
        return preg_replace('/\s+/', '-', $titleToClean);
    }

    public function setNewTrick($trick, $title, $user)
    {
        $trick->setTitle(strtolower($title))
                    ->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime())
                    ->setDefaultImage('images/default-image.jpg')
                    ->setUser($user);
        return $trick;
    }
}