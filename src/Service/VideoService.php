<?php

namespace App\Service;

use App\Entity\Video;

class VideoService
{
    public const REGEX_VIDEO = '#^(https://www.(youtube|dailymotion).com)#';

    public function setNewVideo(string $link, object $trick): object
    {
        $video = new Video();
        $video->setTrick($trick)->setName($link);
        return $video;
    }

    public function setExistingVideo(string $newLink, object $video): object
    {
        $video->setName($newLink);
        return $video;
    }

    public function isSourceAllowed($link)
    {
        return preg_match(self::REGEX_VIDEO, $link);
    }

    public function makeLinkToEmbed($link)
    {
        if (preg_match('#youtube#', $link)) {
            $splitedLink = preg_split('#&#', $link);
            $embeddingLink = preg_replace('#watch\?v=#', 'embed/', $splitedLink[0]);
            return $embeddingLink;
        } elseif (preg_match('#dailymotion#', $link)) {
            $embeddingLink = preg_replace('#video#', 'embed/video', $link);
            return $embeddingLink;
        }
    }
}