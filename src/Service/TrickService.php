<?php

namespace App\Service;

class TrickService
{
    public function cleanUpTitle($titleToClean)
    {
        return preg_replace('/\s+/', '-', $titleToClean);
    }

    public function setTrick($trick, $title, $user)
    {
        if ($trick->getCreatedAt() === null) {
            $trick->setCreatedAt(new \DateTime())
                  ->setDefaultImage('images/default-image.jpg')
                  ->setUser($user);
        }
        $trick->setTitle(strtolower($title))
              ->setUpdatedAt(new \DateTime());
        return $trick;
    }
}